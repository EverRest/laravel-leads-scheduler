<?php
declare(strict_types=1);

namespace App\Console\Commands;

use App\Repositories\LeadRepository;
use App\Services\Lead\LeadRedirectService;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class GenerateScreenShots extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lead:generate-screen-shots {leadId?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @param LeadRepository $leadRepository
     * @param LeadRedirectService $leadRedirectService
     * @return void
     * @throws FileNotFoundException
     */
    public function handle(
        LeadRepository      $leadRepository,
        LeadRedirectService $leadRedirectService,
    ): void
    {
        if ($this->argument('leadId')) {
            $leadId = intval($this->argument('leadId'));
            $lead = $leadRepository->findOrFail($leadId);
            $leadRedirectService->generateScreenshotByLeadRedirect($lead);
            $leads = Collection::make();
            $leads->push($lead);
        } else {
            $leads = $leadRepository->query()
                ->whereHas('leadResult')
                ->where('scheduled_at', '<', Carbon::now()->addMinutes(1)->toDateTimeString())
                ->where('scheduled_at', '>', Carbon::now()->addMinutes(1)->toDateTimeString())
            ->get();
        }
        foreach ($leads as $lead) {
            Log::info('GenerateScreenShots: ' . $lead->id . ' lead');
            $leadRedirectService->generateScreenshotByLeadRedirect($lead);
            Log::info('GenerateScreenShots: ' . $lead->id . ' lead');
            sleep(10);
        }
    }
}

