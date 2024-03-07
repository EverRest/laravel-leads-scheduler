<?php
declare(strict_types=1);

namespace App\Jobs;

use App\Services\Lead\LeadProxyService;
use Exception;

class CreateLeadProxyJob extends LeadJob
{
    /**
     * Execute the job.
     * @throws Exception
     */
    public function handle(LeadProxyService $leadProxyService): void
    {
        $leadProxyService->createProxyByLead($this->lead);
    }
}
