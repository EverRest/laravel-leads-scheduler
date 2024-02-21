<?php
declare(strict_types=1);

namespace App\Jobs;

use App\Repositories\LeadRepository;
use App\Services\AstroProxyService;
use App\Services\LeadServiceFactory;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class SendLead implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private readonly int $leadId)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(
        LeadRepository $leadRepository,
        AstroProxyService $astroProxyService,
    ): void
    {
        try {
            $lead = $leadRepository->findOrFail($this->leadId);
            $astroProxyService->checkNeededProxy($lead->country, []);
            $service = LeadServiceFactory::createService($lead->partner->external_id);
            $tmpLink = $service->send($this->leadId);
            $response = Http::get('localhost:4000', [
                'url' => $tmpLink,
            ]);
        } catch (Exception|GuzzleException $e) {
            $this->fail($e);
        }
    }
}
