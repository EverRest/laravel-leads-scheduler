<?php
declare(strict_types=1);

namespace App\Services;

use App\Helpers\Base64ToUploadedFile;
use App\Models\Lead;
use App\Models\LeadRedirect;
use App\Repositories\LeadRedirectRepository;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class LeadRedirectService
{
    /**
     * @param LeadRedirectRepository $leadRedirectRepository
     */
    public function __construct(
        private readonly LeadRedirectRepository $leadRedirectRepository,
    )
    {
    }

    /**
     * @param Lead $lead
     *
     * @return Model
     * @throws Exception
     */
    public function getRedirectLink(Lead $lead): Model
    {
        try {
            $service = LeadServiceFactory::createService($lead->partner->external_id);
            $redirectLink = $service->send($lead);
        } catch (Exception $e) {
            Log::error(get_class($this) . ": Redirect link was not generated for lead $lead->id. Reason: {$e->getMessage()}");
        }

        return $this->leadRedirectRepository->store([
            'lead_id' => $lead->id,
            'link' => $redirectLink ?? null,
        ]);
    }

    /**
     * @param LeadRedirect $leadRedirect
     *
     * @return Model
     */
    public function generateScreenshotByLeadRedirect(LeadRedirect $leadRedirect): Model
    {
        $lead = $leadRedirect->lead;
        if (!$leadRedirect->link) {
            $message = "Can't find redirect link for lead: {$lead->id}";
            Log::error($message);
        }
        $leadProxy = $lead->leadProxy;
        if (!$leadProxy) {
            $message = "Can't find proxy for lead: $lead->id";
            Log::error($message);
        }
        ['host' => $host, 'port' => $port] = Config::get('browser');
        try {
            $response = Http::post("$host:$port/browser", [
                'url' => $leadRedirect->link,
            ]);
            $screenShot = Arr::get($response?->json(), 'screenshot');
            $uploadedFile = (new Base64ToUploadedFile($screenShot))->file();
            if($uploadedFile->isValid()){
                $fileName = "screenshots/$lead->id.png";
                Storage::disk('public')->exists($fileName) && Storage::disk('public')->delete($fileName);
                Storage::disk('public')->put($fileName, $uploadedFile->get());
                $leadRedirect = $this->leadRedirectRepository->update(
                    $leadRedirect,
                    ['screenshot' => $fileName,],
                );
            }
        } catch (Exception $e) {
            Log::error(get_class($this) . ": Screenshot was not generated for lead $lead->id. Reason: {$e->getMessage()}");
        }
        return $leadRedirect;
    }
}
