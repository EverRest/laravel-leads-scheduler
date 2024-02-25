<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Lead;
use App\Repositories\LeadRepository;
use Illuminate\Support\Facades\Log;

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
        $chatId = '';
        if ($this->leadRepository->getBatchResult($lead->import)) {
            $leads = $this->leadRepository->getLeadsByImport($lead->import);
            Log::info(get_class($this) . ': Batch ' . $lead->import . ' has been finalized with ' . $leads->count() . ' leads.');
//            Telegram::sendMessage(
//                [
//                    'chat_id' => $chatId,
//                    'text' => "Batch $lead->import has been finalized with {$leads->count()} leads.",
//                ]
//            );
        }
        Log::info(get_class($this) . ': Job batch finished.');
    }
}
