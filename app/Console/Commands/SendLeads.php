<?php
declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Lead;
use App\Models\LeadRedirect;
use App\Repositories\LeadRepository;
use App\Services\Lead\LeadBatchService;
use App\Services\Lead\LeadProxyService;
use App\Services\Lead\LeadRedirectService;
use Exception;
use Illuminate\Console\Command;
use Throwable;

class SendLeads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leads:send';

    /**
     * @param LeadRepository $leadRepository
     * @param LeadProxyService $leadProxyService
     * @param LeadRedirectService $leadRedirectService
     * @param LeadBatchService $leadBatchService
     */
    public function __construct(
        private readonly LeadRepository      $leadRepository,
        private readonly LeadProxyService $leadProxyService,
        private readonly LeadRedirectService $leadRedirectService,
        private readonly LeadBatchService $leadBatchService,
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
        $leads = $this->leadRepository->getLeadsToSend();
        $leads->each(fn(Lead $lead) => $this->sendLead($lead));
    }

    /**
     * @param Lead $lead
     *
     * @throws Exception
     * @throws Throwable
     */
    private function sendLead(Lead $lead): void
    {
        $proxy= $this->leadProxyService->createProxyByLead($lead);
        /** @var LeadRedirect $leadRedirect */
        $leadRedirect = $this->leadRedirectService->getRedirectLink($proxy->lead);
        $result = $this->leadRedirectService->generateScreenshotByLeadRedirect($leadRedirect);
        $this->leadProxyService->deleteProxyByLead($result->lead);
        $this->leadBatchService->closeBatchByLead($lead);
    }
}
