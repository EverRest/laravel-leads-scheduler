<?php
declare(strict_types=1);

namespace App\Jobs;

use App\Models\Lead;
use App\Models\LeadProxy;
use App\Repositories\LeadProxyRepository;
use App\Repositories\LeadRepository;
use App\Services\AstroService;
use App\Services\LeadResultService;
use App\Services\LeadServiceFactory;
use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\Response;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendLead implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param int $leadId
     */
    public function __construct(private readonly int $leadId)
    {
    }

    /**
     * @param LeadRepository $leadRepository
     * @param LeadResultService $leadResultService
     *
     * @return void
     */
    public function handle(
        LeadRepository    $leadRepository,
        LeadResultService $leadResultService,
    ): void
    {
        try {
            /** @var Lead $lead */
            $lead = $leadRepository->findOrFail($this->leadId);
            if (!$lead->leadProxy) {
                Log::error("$lead->id Lead proxy not found.");
                $this->fail(new Exception("$lead->id Lead proxy not found."));
            }
            $service = LeadServiceFactory::createService($lead->partner->external_id);
            $tmpLink = $service->send($lead);
            $response = $this->postBrowser($tmpLink, $lead->leadProxy);
            if ($response->failed()) {
                Log::error('Failed to send lead: ' . $lead->id);
                $this->fail(new Exception("$lead->id Lead proxy not found."));
            }
            $this->storeLeadResult($lead, $response, $tmpLink, $leadResultService);
            $leadRepository->patch($lead, 'is_sent', true);
            Log::info(get_class($this) . ': Lead sent ' . $lead->id . '.');
        } catch (Exception $e) {
            $this->fail($e);
        }
    }

    /**
     * @param string $tmpLink
     * @param LeadProxy $leadProxy
     *
     * @return Response
     */
    private function postBrowser(string $tmpLink, LeadProxy $leadProxy): Response
    {
        return Http::post('localhost:4000/browser', [
            'url' => $tmpLink,
            'proxy' => [
                'port' => $leadProxy->port,
                'protocol' => $leadProxy->protocol,
                'host' => $leadProxy->host,
                'username' => $leadProxy->username,
                'password' => $leadProxy->password,
            ]
        ]);
    }

    /**
     * @param Lead $lead
     * @param $response
     * @param string $tmpLink
     * @param LeadResultService $leadResultService
     *
     * @return void
     * @throws Exception
     */
    private function storeLeadResult(Lead $lead, $response, string $tmpLink, LeadResultService $leadResultService): void
    {
        $json = $response->json();
        $screenShot = Arr::get($json, 'screenshot');

        $leadResultService->store([
            'lead_id' => $lead->id,
            'file' => $screenShot,
            'status' => 200,
            'data' => [
                'response' => $response->body(),
                'link' => $tmpLink,
            ],
        ]);
    }
}
