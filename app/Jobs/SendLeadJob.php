<?php

namespace App\Jobs;

use App\Models\LeadRedirect;
use App\Services\Lead\LeadRedirectService;
use App\Services\Partner\PartnerServiceFactory;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class SendLeadJob extends LeadJob
{
    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public int $tries = 1;

    /**
     * Execute the job.
     * @throws FileNotFoundException
     */
    public function handle(LeadRedirectService $leadRedirectService): void
    {
        /** @var LeadRedirect $leadRedirect */
        $service = PartnerServiceFactory::createService($this->lead->partner->external_id);
        $service->send($this->lead);
//        $leadRedirectService->generateScreenshotByLeadRedirect($this->lead);
    }
}
