<?php
declare(strict_types=1);

namespace App\Jobs;

use App\Models\Lead;
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
     */
    public function  __construct(private readonly int $leadId)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(
        LeadRepository      $leadRepository,
        AstroService        $astroService,
        LeadProxyRepository $leadProxyRepository,
        LeadResultService   $leadResultService,
    ): void
    {
        try {
            /** @var Lead $lead */
            $lead = $leadRepository->findOrFail($this->leadId);
            $proxy = $this->pickUpProxyByCountry($astroService, $leadProxyRepository, $lead);
            $this->sendLead($lead, $leadResultService);
            $astroService->deletePort(Arr::get($proxy, 'id'));
        } catch (Exception $e) {
            $this->fail($e);
        }
    }

    /**
     * @param Lead $lead
     * @param LeadResultService $leadResultService
     *
     * @return void
     * @throws Exception
     */
    private function sendLead(Lead $lead, LeadResultService $leadResultService): void
    {
        if (!$lead->leadProxy->ip) {
            Log::error("$lead->id Lead proxy not found.");
        }
        $service = LeadServiceFactory::createService($lead->partner->external_id);
        $tmpLink = $service->send($lead);
        $response = Http::post('localhost:4000/browser', [
            'url' => $tmpLink,
        ]);
        Log::info('Response: ' . $response->body());
        if ($response->failed()) {
            Log::error('Failed to send lead: ' . $lead->id);
        }
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


    /**
     * @param AstroService $astroService
     * @param LeadProxyRepository $leadProxyRepository
     * @param Lead $lead
     *
     * @return array
     * @throws Exception
     */
    private function pickUpProxyByCountry(AstroService $astroService, LeadProxyRepository $leadProxyRepository, Lead $lead): array
    {
        $country = $astroService->getCountryByISO2($lead->country);
        if (!$country) {
            Log::error(Carbon::now()->format('Y-m-d H:i:s') . ' Country not found in the proxy list.');
        }
        $port = $astroService->createPortByLead($country, $lead);
        Log::info($lead->id . ' Random port: ' . Arr::get($port, 'id') . ' for country: ' . $lead->country);
        $proxy = $astroService->setProxy($port);
        Log::info($lead->id . ' Picked up proxy: ' . Arr::get($proxy, 'host') . '' . Arr::get($proxy, 'port') . ' for country: ' . $country);
        $ip = $astroService->newIp(Arr::get($port, 'id'));
        Log::info($lead->id . ' Picked up ip: ' . $ip);
        Arr::set($proxy, 'ip', $ip);
        $leadProxyRepository->firstOrCreate([
            'lead_id' => $lead->id,
            'ip' => $ip,
            'external_id' => Arr::get($proxy, 'id'),
            'port' => Arr::get($proxy, 'port'),
            'protocol' => Arr::get($proxy, 'protocol'),
            'username' => Arr::get($proxy, 'username'),
            'password' => Arr::get($proxy, 'password'),
            'host' => Arr::get($proxy, 'host'),
            'country' => $country,
        ]);

        return $proxy;
    }
}
