<?php
declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Lead;
use App\Models\LeadRedirect;
use App\Repositories\LeadRepository;
use App\Services\Lead\LeadBatchService;
use App\Services\Lead\LeadRedirectService;
use App\Services\Partner\PartnerServiceFactory;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Collection;
use Throwable;

class SendLeads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature     = 'leads:send  {leadId?}';

    /**
     * @param LeadRepository $leadRepository
     * @param LeadRedirectService $leadRedirectService
     */
    public function __construct(
        private readonly LeadRepository $leadRepository,
        private readonly LeadRedirectService $leadRedirectService,
    )
    {
        parent::__construct();
    }

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @throws Throwable
     */
    public function handle(): void
    {

        if($this->argument('leadId')) {
            $leadId = intval($this->argument('leadId'));
            $lead = $this->leadRepository->findOrFail($leadId);
            $leads = Collection::make();
            $leads->push($lead);
        } else {
            $leads = $this->leadRepository->getLeadsWithoutProxy();
        }
        $leads->each(
            fn(Lead $lead) => $this->sendLead($lead,)
        );
    }

    /**
     * @param Lead $lead
     *
     * @throws FileNotFoundException
     * @throws Exception
     */
    private function sendLead(
        Lead                $lead,
    ): void
    {
        /** @var LeadRedirect $leadRedirect */
        $service = PartnerServiceFactory::createService($lead->partner->external_id);
        $leadRedirect =  $service->send($lead);
        $this->leadRedirectService->generateScreenshotByLeadRedirect($lead->leadRedirect);
        $isBatchClosed = $this->leadRepository->getBatchResult($lead->import);
//        if ($isBatchClosed) {
//            $this->leadBatchService->closeBatchByLead($lead);
//        }
    }
}
