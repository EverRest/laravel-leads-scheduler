<?php
declare(strict_types=1);

namespace App\Services\Lead;

use App\Models\Lead;
use App\Repositories\LeadRepository;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;

final class LeadBatchService
{
    /**
     * LeadBatchService constructor.
     *
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
        $chatId = $this->getChatId();
        if ($this->leadRepository->getBatchResult($lead->import)) {
            $leads = $this->leadRepository->getLeadsByImport($lead->import);
            $failedLeadsCount = $this->countFailedLeads($leads);
            $successfulLeadsCount = $this->countSuccessfulLeads($leads);
            $this->logBatchFinalization($lead, $leads);
            $this->sendBatchFinalizationMessage($chatId, $lead, $leads, $successfulLeadsCount, $failedLeadsCount);
        }
        $this->logJobBatchFinished();
    }

    /**
     * @return string
     */
    private function getChatId(): string
    {
        $updates = Telegram::getUpdates();
        return data_get($updates, 'result.0.message.chat.id')?? Config::get('services.telegram.chat_id');
    }

    /**
     * @param $leads
     *
     * @return int
     */
    private function countFailedLeads($leads): int
    {
        return $leads->filter(fn(Lead $lead) => $lead->leadResult?->status === 201 )->count();
    }

    /**
     * @param $leads
     *
     * @return int
     */
    private function countSuccessfulLeads($leads): int
    {
        return $leads->filter(fn(Lead $lead) => $lead->leadResult?->status !== 201 )->count();
    }

    /**
     * @param Lead $lead
     * @param $leads
     *
     * @return void
     */
    private function logBatchFinalization(Lead $lead, $leads): void
    {
        Log::info(get_class($this) . ': Batch ' . $lead->import . ' has been finalized with ' . $leads->count() . ' leads.');
    }

    /**
     * @param string $chatId
     * @param Lead $lead
     * @param $leads
     * @param int $successfulLeadsCount
     * @param int $failedLeadsCount
     *
     * @return void
     */
    private function sendBatchFinalizationMessage(string $chatId, Lead $lead, $leads, int $successfulLeadsCount, int $failedLeadsCount): void
    {
        Telegram::sendMessage(
            [
                'chat_id' => $chatId,
                'text' =>   "Batch $lead->import відправив {$leads->count()} лідів. Успішних: $successfulLeadsCount, Неуспішних: $failedLeadsCount",
            ]
        );
    }

    /**
     * @return void
     */
    private function logJobBatchFinished(): void
    {
        Log::info(get_class($this) . ': Job batch finished.');
    }
}