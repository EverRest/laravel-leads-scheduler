<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Lead;
use App\Repositories\LeadRepository;
use App\Services\Lead\LeadRedirectService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

class GenerateScreenShotJob
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Lead $lead
     */
    protected Lead $lead;

    /**
     * Create a new job instance.
     */
    public function __construct(protected int $leadId)
    {
        $repository = App::make(LeadRepository::class);
        $this->lead = $repository->findOrFail($leadId);
    }

    /**
     * Execute the job.
     * @throws FileNotFoundException
     */
    public function handle(LeadRedirectService $leadRedirectService): void
    {
        $leadRedirectService->generateScreenshotByLeadRedirect($this->lead);
    }
}
