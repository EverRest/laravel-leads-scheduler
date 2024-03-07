<?php
declare(strict_types=1);

namespace App\Console\Commands;

use App\Repositories\LeadRepository;
use App\Services\Lead\LeadRedirectService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Throwable;

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
     *
     * @return void
     * @throws Throwable
     */
    public function handle(
        LeadRepository       $leadRepository,
        LeadRedirectService  $leadRedirectService,
    ): void
    {

        if($this->argument('leadId')) {
            $leadId = intval($this->argument('leadId'));
            $lead = $leadRepository->findOrFail($leadId);
            $leads = Collection::make();
            $leads->push($lead);
        } else {
            $leads = $leadRepository->getLeadsWithRedirects();
        }
        foreach ($leads as $lead) {
            $leadRedirectService->generateScreenshotByLeadRedirect($lead->leadRedirect);
        }
    }
}
