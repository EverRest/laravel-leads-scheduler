<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Helpers\Base64ToUploadedFile;
use App\Models\Lead;
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
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

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
                ->whereIn('status', [ResponseAlias::HTTP_OK, ResponseAlias::HTTP_CREATED])
                ->whereNotNull('link')
                ->whereNull('file')
                ->where('scheduled_at', '>=', Carbon::now()
                    ->subHour()
                    ->toDateTimeString()
                )
                ->where('scheduled_at', '>=', Carbon::today()
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
        $link = Arr::get($lead->data ?? [], $lead->partner->dto_redirect_key);
        Log::info('LinkKey: ' . $lead->partner->dto_redirect_key . ' lead');
        Log::info('Link: ' . $link . ' lead');
        if ($link) {
            /** @var Lead $lead */
            $lead = $leadRepository->patch($lead, 'link', $link);
            $response = $this->getBrowserResponse($lead);
            $screenShot = Arr::get($response?->json() ?? [], 'screenshot');
            $uploadedFile = $screenShot ? (new Base64ToUploadedFile($screenShot))->file() : null;
            if ($uploadedFile && $uploadedFile->isValid()) {
                return $this->storeScreenshot($lead, $uploadedFile, $leadRepository);
            }
        }
        Log::error('No redirect link for lead ' . $lead->id);

        return null;
    }

    /**
     * @param Lead $lead
     *
     * @return Response|null
     */
    private function getBrowserResponse(Lead $lead): ?Response
    {
        $browserHost = Config::get('services.puppeteer.url');
        $browserPort = Config::get('services.puppeteer.port');

        try {
            $proxy = [
                'host' => $lead->host,
                'port' => $lead->port,
                'protocol' => $lead->protocol,
                'username' => $lead->first_name,
                'password' => $lead->password,
            ];
            return Http::post(
                "http://$browserHost:$browserPort/browser",
                ['url' => $lead->link, 'proxy' => $proxy]
            );
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

