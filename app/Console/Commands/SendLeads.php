<?php
declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\FinalizeBatch;
use App\Jobs\SendLead;
use App\Repositories\LeadRepository;
use Illuminate\Bus\Batch;
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
        $batch = [];
        foreach ($leads as $lead) {
            $batch[] = [
                new SendLead($lead->id),
                new FinalizeBatch($lead->import),
            ];
        }
        $this->sendLead($batch);
    }

    /**
     * @param array $batch
     * @throws Throwable
     */
    private function sendLead(array $batch): void
    {
        Bus::batch($batch)
            ->then(fn (Batch $batch) => Log::info($batch->name . ': Batch finished.'))
            ->dispatch();
    }
}
