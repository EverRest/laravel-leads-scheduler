<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\Lead\LeadRedirectService;

class GenerateScreenShotJob extends LeadJob
{
    /**
     * Execute the job.
     */
    public function handle(LeadRedirectService $leadRedirectService): void
    {
        $leadRedirectService->generateScreenshotByLeadRedirect($this->lead->leadRedirect);
    }
}
