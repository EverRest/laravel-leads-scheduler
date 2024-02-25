<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Lead;
use App\Models\LeadRedirect;
use App\Repositories\LeadRedirectRepository;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
        try {
            $response = Http::post('localhost:4000/browser', [
                'url' => $leadRedirect->link,
                'proxy' => [
                    'port' => $leadProxy->port,
                    'protocol' => $leadProxy->protocol,
                    'host' => $leadProxy->host,
                    'username' => $leadProxy->username,
                    'password' => $leadProxy->password,
                ]
            ]);
            $leadRedirect =$this->leadRedirectRepository->update
            ($leadRedirect->id,
                [
                    'screenshot' => Arr::get($response?->json(), 'screenshot'),
                ]
            );
        } catch (Exception $e) {
            Log::error(get_class($this) . ": Screenshot was not generated for lead $lead->id. Reason: {$e->getMessage()}");
        }
        return  $leadRedirect;
    }
}
