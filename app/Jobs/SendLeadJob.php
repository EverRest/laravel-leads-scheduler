<?php

namespace App\Jobs;

use App\Models\LeadRedirect;
use App\Services\Partner\PartnerServiceFactory;

class SendLeadJob extends LeadJob
{
    /**
     * Execute the job.
     */
    public function handle(): void
    {
        /** @var LeadRedirect $leadRedirect */
        $service = PartnerServiceFactory::createService($this->lead->partner->external_id);
        $service->send($this->lead);
    }
}
