<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\Partner\PartnerServiceFactory;

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
     */
    public function handle(): void
    {
        $service = PartnerServiceFactory::createService($this->lead->partner->external_id);
        $service->send($this->lead);
    }
}
