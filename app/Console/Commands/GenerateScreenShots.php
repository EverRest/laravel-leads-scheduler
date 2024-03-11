<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Helpers\Base64ToUploadedFile;
use App\Models\Lead;
use App\Models\LeadRedirect;
use App\Repositories\LeadRepository;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GenerateScreenShots extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lead:generate-screen-shots {leadId?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @param LeadRepository $leadRepository
     *
     * @return void
     * @throws FileNotFoundException
     */
    public function handle(
        LeadRepository $leadRepository,
    ): void
    {
        if ($this->argument('leadId')) {
            $leadId = intval($this->argument('leadId'));
            $lead = $leadRepository->findOrFail($leadId);
            $leads = Collection::make();
            $leads->push($lead);
        } else {
            $leads = $leadRepository->query()
                ->whereHas('leadResult', fn($q) => $q->whereNotNull('data'))
                ->where('scheduled_at', '>=', Carbon::now()->subMinutes(5)
                    ->toDateTimeString())
                ->get();
        }
        foreach ($leads as $lead) {
            $this->generateScreenshotByLeadRedirect($lead, $leadRepository);
            Log::info('GenerateScreenShots: ' . $lead->id . ' lead');
        }
    }

    /**
     * @param Lead $lead
     * @param LeadRepository $leadRepository
     *
     * @return ?Model
     * @throws FileNotFoundException
     */
    public function generateScreenshotByLeadRedirect(Lead $lead, LeadRepository $leadRepository): ?Model
    {
        $link = Arr::get($lead->data ?? [], $lead->redirectLinkKey);
        Log::info('LinkKey: ' . $lead->redirectLinkKey . ' lead');
        Log::info('Link: ' . $link . ' lead');
        if ($link) {
            /** @var Lead $lead */
            $lead = $leadRepository->patch($lead, 'link', $link);
            Log::info('Lead: ' . json_encode($lead->toArray()));
            $response = $this->getBrowserResponse($lead);
            $screenShot = Arr::get($response?->json() ?? [], 'screenshot');
            Log::info("Screenshot: " . $screenShot);
            Log::info("Response: " . $response);
            $uploadedFile = $screenShot ? (new Base64ToUploadedFile($screenShot))->file() : null;
            if ($uploadedFile && $uploadedFile->isValid()) {
                return $this->storeScreenshot($lead, $uploadedFile, $leadRepository);
            }
        }
        Log::error('No redirect link for lead ' . $lead->id);

        return null;
    }

    /**
     * @param LeadRedirect $leadRedirect
     *
     * @return bool
     */
    private function validateLeadRedirect(LeadRedirect $leadRedirect): bool
    {
        $lead = $leadRedirect->lead;
        if (!$leadRedirect->link) {
            Log::error("Can't find redirect link for lead: {$lead->id}");
            return false;
        }
        if (!$lead->leadProxy) {
            Log::error("Can't find proxy for lead: $lead->id");
            return false;
        }
        return true;
    }

    /**
     * @param Lead $lead
     *
     * @return Response|null
     */
    private function getBrowserResponse(Lead $lead): ?Response
    {
        try {
            $proxy = [
                'host' => $lead->host,
                'port' => $lead->port,
                'protocol' => $lead->protocol,
                'username' => $lead->first_name,
                'password' => $lead->password,
                'link' => $lead->link,
            ];
            return Http::post("http://localhost:4000/browser", [
                'url' => $lead->llink,
                'proxy' => $proxy
            ]);
        } catch (Exception $e) {
            Log::error(get_class($this) . ": Screenshot was not generated for lead {$lead->id}. Reason: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * @param Lead $lead
     * @param UploadedFile $uploadedFile
     * @param LeadRepository $leadRepository
     *
     * @return Model
     * @throws FileNotFoundException
     */
    private function storeScreenshot(Lead $lead, UploadedFile $uploadedFile, LeadRepository $leadRepository): Model
    {
        $fileName = "screenshots/$lead->id.png";
        Storage::disk('public')->exists($fileName) && Storage::disk('public')->delete($fileName);
        Storage::disk('public')->put($fileName, $uploadedFile->get());

        return $leadRepository->patch($lead, 'file', $fileName);
    }
}

