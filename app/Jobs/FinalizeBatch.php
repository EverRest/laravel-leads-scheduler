<?php
declare(strict_types=1);

namespace App\Jobs;

use App\Repositories\LeadRepository;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Telegram\Bot\Laravel\Facades\Telegram;

class FinalizeBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
                Telegram::sendMessage(
                    [
                        'chat_id' => $chatId,
                        'text' => "Batch $this->import has been finalized with {$leads->count()} leads.",
                    ]
                );
            }
        } catch (Exception $e) {
            $this->fail($e);
        }
    }
}
