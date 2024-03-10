<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Helpers\Base64ToUploadedFile;
use App\Models\LeadRedirect;
use App\Models\LeadResult;
use App\Repositories\LeadRedirectRepository;
use App\Repositories\LeadResultRepository;
use App\Services\Lead\LeadRedirectService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GenerateScreenShotFromLeadResultJob
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private LeadResultRepository $leadResultRepository;
        private LeadRedirectRepository $leadRedirectRepository;
        private LeadRedirectService $leadRedirectService;

    public function __construct(
    )
    {
        $this->leadResultRepository = App::make(LeadResultRepository::class);
        $this->leadRedirectRepository = App::make(LeadRedirectRepository::class);
        $this->leadRedirectService = App::make(LeadRedirectService::class);
    }

    /**
     * Execute the job.
     * @throws FileNotFoundException
     * @throws Exception
     */
    public function handle(LeadResult $leadResult): void
    {
        $link = Arr::get($leadResult->toArray() ?? [], $leadResult->lead->redirectLinkKey);
        Log::info('GenerateScreenshotByLeadRedirect: ' . $leadResult->lead->id . ' lead');
        Log::info('LinkKey: ' . $leadResult->lead->redirectLinkKey. ' lead');
        Log::info('Link: ' . $link. ' lead');
        if($link) {
            /** @var LeadRedirect $leadRedirect */
            $leadRedirect = $this->leadRedirectRepository->firstOrCreate(['lead_id' => $leadResult->lead->id, 'link' => $link]);
            $response = $this->getBrowserResponse($leadRedirect);
            $screenShot = Arr::get($response?->json() ?? [], 'screenshot');
            $uploadedFile = $screenShot ? (new Base64ToUploadedFile($screenShot))->file() : null;
            if ($uploadedFile && $uploadedFile->isValid()) {
                $this->storeScreenshot($leadRedirect, $uploadedFile);
            }
            Log::error($uploadedFile->getErrorMessage());
        }
        throw new Exception('Failed to create file for lead ' . $leadResult->lead->id);
    }

    /**
     * @param LeadRedirect $leadRedirect
     * @param UploadedFile $uploadedFile
     *
     * @return Model
     * @throws FileNotFoundException
     */
    private function storeScreenshot(LeadRedirect $leadRedirect, UploadedFile $uploadedFile): Model
    {
        $lead = $leadRedirect->lead;
        $fileName = "screenshots/$lead->id.png";
        Storage::disk('public')->exists($fileName) && Storage::disk('public')->delete($fileName);
        Storage::disk('public')->put($fileName, $uploadedFile->get());

        return $this->leadRedirectRepository->update(
            $leadRedirect,
            ['file' => $fileName,],
        );
    }

    /**
     * @param LeadRedirect $leadRedirect
     *
     * @return Response|null
     */
    private function getBrowserResponse(LeadRedirect $leadRedirect): ?Response
    {
        try {
            $link = $leadRedirect->link;
            $proxy = [
                'host' => $leadRedirect->lead->leadProxy->host,
                'port' => $leadRedirect->lead->leadProxy->port,
                'protocol' => $leadRedirect->lead->leadProxy->protocol,
                'username' => $leadRedirect->lead->leadProxy->username,
                'password' => $leadRedirect->lead->leadProxy->password,
            ];
            Log::info("Link: $link");
            Log::info("Proxy: " . json_encode($proxy));
            return Http::post("http://localhost:4000/browser", [
                'url' => $link,
                'proxy' => $proxy
            ]);
        } catch (Exception $e) {
            Log::error(get_class($this) . ": Screenshot was not generated for lead {$leadRedirect->lead->id}. Reason: {$e->getMessage()}");
            return null;
        }
    }
}
