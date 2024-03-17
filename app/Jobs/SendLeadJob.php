<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\Partner\PartnerServiceFactory;
use Illuminate\Support\Facades\Artisan;

class SendLeadJob extends LeadJob
{
    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public int $tries = 1;

    /**
     * @var int
     */
    public int $timeout = 300;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $service = PartnerServiceFactory::createService($this->lead->partner->external_id);
        $link = $service->send($this->lead);
//        if($link) Artisan::call('lead:generate-screen-shots', ['leadId' => $this->lead->id]);
    }
}
