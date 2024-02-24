<?php
declare(strict_types=1);

namespace App\Jobs;

use App\Repositories\LeadRepository;
use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;

class FinalizeBatch implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private readonly string $import)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(LeadRepository $leadRepository): void
    {
        try {
            $chatId = '';
            if ($leadRepository->getBatchResult($this->import)) {
                $leads = $leadRepository->getLeadsByImport($this->import);
                Log::info(get_class($this) . ': Batch ' . $this->import . ' has been finalized with ' . $leads->count() . ' leads.');
                Telegram::sendMessage(
                    [
                        'chat_id' => $chatId,
                        'text' => "Batch $this->import has been finalized with {$leads->count()} leads.",
                    ]
                );
            }
            Log::info(get_class($this) . ': Job batch finished.');
        } catch (Exception $e) {
            $this->fail($e);
        }
    }
}
