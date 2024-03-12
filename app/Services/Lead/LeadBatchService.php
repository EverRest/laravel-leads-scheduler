<?php

declare(strict_types=1);

namespace App\Services\Lead;

use App\Models\Lead;
use App\Repositories\LeadRepository;
use Illuminate\Support\Facades\Config;
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
        $leads = $this->leadRepository->getLeadsByImport($lead->import);
        $failedLeadsCount = $this->countFailedLeads($leads);
        $successfulLeadsCount = $this->countSuccessfulLeads($leads);
        $this->sendBatchFinalizationMessage($chatId, $lead, $leads, $successfulLeadsCount, $failedLeadsCount);
    }

    /**
     * @return string
     */
    private function getChatId(): string
    {
        return Config::get('services.telegram.chat_id');
    }

    /**
     * @param $leads
     *
     * @return int
     */
    private function countFailedLeads($leads): int
    {
        return $leads->filter(fn(Lead $lead) => !in_array($lead->leadResult?->status, ['200', '201']))->count();
    }

    /**
     * @param $leads
     *
     * @return int
     */
    private function countSuccessfulLeads($leads): int
    {
        return $leads->filter(fn(Lead $lead) => in_array($lead->leadResult?->status, ['200', '201']))->count();
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
                'text' => "Batch $lead->import відправив партнеру {$lead->partner->name} {$leads->count()} лідів. Успішних: $successfulLeadsCount, Неуспішних: $failedLeadsCount",
            ]
        );
    }
}
