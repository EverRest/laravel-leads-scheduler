<?php
declare(strict_types=1);

namespace App\Jobs;

use App\Services\Proxy\AstroService;

class DeleteLeadProxyJob extends LeadJob
{
    /**
     * Execute the job.
     */
    public function handle(AstroService $astroService): void
    {
        $astroService->deletePort($this->lead->proxy_external_id);
    }
}
