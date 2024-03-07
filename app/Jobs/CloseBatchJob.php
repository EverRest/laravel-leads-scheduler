<?php
declare(strict_types=1);

namespace App\Jobs;

use App\Services\Lead\LeadBatchService;

class CloseBatchJob extends LeadJob
{
    /**
     * Execute the job.
     */
    public function handle(LeadBatchService $leadBatchService): void
    {
        $leadBatchService->closeBatchByLead($this->lead);
    }
}
