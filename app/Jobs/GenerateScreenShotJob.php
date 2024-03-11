<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\Lead\LeadRedirectService;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class GenerateScreenShotJob extends LeadJob
{
    /**
     * Execute the job.
     * @throws FileNotFoundException
     */
    public function handle(LeadRedirectService $leadRedirectService): void
    {
        $leadRedirectService->generateScreenshotByLeadRedirect($this->lead);
    }
}
