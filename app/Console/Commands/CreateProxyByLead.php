<?php
declare(strict_types=1);

namespace App\Console\Commands;

use App\Repositories\LeadRepository;
use App\Services\Lead\LeadProxyService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class CreateProxyByLead extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lead:create-proxy {leadId?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @throws Exception
     */
    public function handle(LeadProxyService $leadProxyService, LeadRepository $leadRepository): void
    {
        if($this->argument('leadId')) {
            $leadId = intval($this->argument('leadId'));
            $lead = $leadRepository->findOrFail($leadId);
            $leads = Collection::make();
            $leads->push($lead);
        } else {
            $leads = $leadRepository->getLeadsWithoutProxy();
        }
        foreach ($leads as $lead) {
            $leadProxyService->createProxyByLead($lead);
        }
    }
}
