<?php
declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\FinalizeBatch;
use App\Jobs\SendLead;
use App\Models\Lead;
use App\Repositories\LeadRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
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
     */
    public function handle(): void
    {
        $leads = $this->leadRepository->getLeadsToSend();
        foreach ($leads as $lead) {
            $this->sendLead($lead);
        }
    }

    /**
     * @param Lead $lead
     */
    private function sendLead(Lead $lead): void
    {
        Bus::chain([
            new SendLead($lead->id),
            new FinalizeBatch($lead->import),
        ])->catch(function (Throwable $e) {
            Log::error($e->getMessage());
        })->dispatch();
    }
}
