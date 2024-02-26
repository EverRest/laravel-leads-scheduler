<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Lead;
use App\Repositories\LeadRepository;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;

class LeadBatchService
{
    /**
     * @param LeadRepository $leadRepository
     */
    public function __construct(
        private readonly LeadRepository $leadRepository,
    )
    {
    }

    /**
     * @param Lead $lead
     *
     * @return void
     */
    public function closeBatchByLead(Lead $lead): void
    {
        $chatId = config('services.telegram.chat_id');
        if ($this->leadRepository->getBatchResult($lead->import)) {
            $leads = $this->leadRepository->getLeadsByImport($lead->import);
            $failedLeadsCount = $leads->filter(fn(Lead $lead) => $lead->leadResult->status === 201 )->count();
            $successfulLeadsCount = $leads->filter(fn(Lead $lead) => $lead->leadResult->status !== 201 )->count();
            Log::info(get_class($this) . ': Batch ' . $lead->import . ' has been finalized with ' . $leads->count() . ' leads.');
            Telegram::sendMessage(
                [
                    'chat_id' => $chatId,
                    'text' =>   "Batch $lead->import відправив {$leads->count()} лідів. Успішних: $successfulLeadsCount, Неуспішних: $failedLeadsCount",
                ]
            );
        }
        Log::info(get_class($this) . ': Job batch finished.');
    }
}
