<?php
declare(strict_types=1);

namespace App\Jobs;

use App\Models\Lead;
use App\Repositories\LeadProxyRepository;
use App\Repositories\LeadRepository;
use App\Services\AstroService;
use App\Services\LeadServiceFactory;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
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
    public function __construct(private readonly int $leadId)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(
        LeadRepository    $leadRepository,
        AstroService      $astroService,
        LeadProxyRepository $leadProxyRepository,
    ): void
    {
        try {
            /** @var Lead $lead */
            $lead = $leadRepository->findOrFail($this->leadId);
            $proxy = $this->pickUpProxyByCountry($astroService, $leadProxyRepository, $lead);
            $this->sendLead($lead);
            $astroService->deletePort(Arr::get($proxy, 'id'));
        } catch (Exception|GuzzleException $e) {
            $this->fail($e);
        }
    }

    /**
     * @param Lead $lead
     *
     * @return void
     */
    private function sendLead(Lead $lead): void
    {
        if(!$lead->leadProxy->ip) {
            Log::error("$lead->id Lead proxy not found.");
        }
        $service = LeadServiceFactory::createService($lead->partner->external_id);
        $tmpLink = $service->send($lead);
        Http::get('localhost:4000', [
            'url' => $tmpLink,
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
            throw new Exception(Carbon::now()->format('Y-m-d H:i:s')  . ' Country not found in the proxy list.');
        }
        $availablePorts = $astroService->getAvailablePorts();
        $countryPorts = $availablePorts->filter(
            fn($port) => Arr::get($port, 'country') === $country
        );
        $randomPort = $countryPorts->isEmpty() ? $astroService->createPortByLead($country, $lead):$countryPorts->random();
        Log::info( ' Random port: ' . Arr::get($randomPort, 'id') . ' for country: ' . $lead->country);
        $proxy = $astroService->setProxy($randomPort);
        Log::info( ' Picked up proxy: ' . Arr::get($proxy, 'host') . '' . Arr::get($proxy, 'port') . ' for country: ' . $country);
        $ip = $astroService->newIp(Arr::get($randomPort, 'id'));
        Log::info( ' Picked up ip: ' . $ip);
        Arr::set($proxy, 'ip', $ip);
        $leadProxyRepository->firstOrCreate([
            'lead_id' => $lead->id,
            'ip' => $ip,
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
