<?php
declare(strict_types=1);

namespace App\Jobs;

use App\Models\Lead;
use App\Repositories\LeadProxyRepository;
use App\Repositories\LeadRepository;
use App\Services\AstroService;
use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class DeleteProxy implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param int $leadId
     */
    public function  __construct(private readonly int $leadId)
    {
    }

    /**
     * @param LeadRepository $leadRepository
     * @param AstroService $astroService
     * @param LeadProxyRepository $leadProxyRepository
     *
     * @return void
     * @throws Throwable
     */
    public function handle(
        LeadRepository      $leadRepository,
        AstroService        $astroService,
        LeadProxyRepository $leadProxyRepository,
    ): void
    {
        try {
            /** @var Lead $lead */
            $lead = $leadRepository->findOrFail($this->leadId);
            if($lead->is_sent) {
                $astroService->deletePort($lead->leadProxy->external_id);
                $leadProxyRepository->destroy($lead->leadProxy);
            }
            Log::info(get_class($this) . ': Port deleted for lead ' . $lead->id . '.');
        } catch (Exception $e) {
            $this->fail($e);
        }
    }
}
