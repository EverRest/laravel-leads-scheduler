<?php
declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\CreateProxy;
use App\Jobs\DeleteProxy;
use App\Jobs\FinalizeBatch;
use App\Jobs\SendLead;
use App\Models\Lead;
use App\Repositories\LeadRepository;
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
     * Create a new command instance.
     *
     * @param LeadRepository $leadRepository
     */
    public function __construct(private readonly LeadRepository $leadRepository)
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
     */
    private function sendLead(Lead $lead): void
    {
        CreateProxy::withChain([
            new SendLead($lead->id),
            new DeleteProxy($lead->id),
            new FinalizeBatch($lead->import),
        ])->dispatch($lead->id);
    }
}
