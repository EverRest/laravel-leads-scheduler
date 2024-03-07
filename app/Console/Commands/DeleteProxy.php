<?php
declare(strict_types=1);

namespace App\Console\Commands;

use App\Repositories\LeadProxyRepository;
use App\Repositories\LeadRepository;
use App\Services\Proxy\AstroService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Throwable;

class DeleteProxy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lead:delete-proxy {leadId?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @param AstroService $astroService
     * @param LeadRepository $leadRepository
     *
     * @return void
     * @throws Throwable
     */
    public function handle(
        AstroService     $astroService,
        LeadRepository   $leadRepository,
    ): void
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
            $astroService->deletePort($lead->leadProxy->external_id);
        }
    }
}
