<?php
declare(strict_types=1);

namespace App\Jobs;

use App\Models\Lead;
use App\Repositories\LeadRepository;
use App\Services\AstroService;
use App\Services\LeadServiceFactory;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
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
        LeadRepository    $leadRepository,
        AstroService      $astroService
    ): void
    {
        try {
            /** @var Lead $lead */
            $lead = $leadRepository->findOrFail($this->leadId);
            $proxy = $this->pickUpProxyByCountry($astroService, $lead->country_code);
            $this->sendLead($lead, Arr::get($proxy, 'ip'));
        } catch (Exception|GuzzleException $e) {
            $this->fail($e);
        }
    }

    /**
     * @param AstroService $astroService
     * @param string $countryCode
     *
     * @return array
     * @throws Exception
     */
    private function pickUpProxyByCountry(AstroService $astroService, string $countryCode): array
    {
        $country = $astroService->getCountryByISO2($countryCode);
        if (!$country) {
            throw new Exception('Country not found in the proxy list.');
        }
        $availablePorts = $astroService->getAvailablePorts();
        $countryPorts = $availablePorts->filter(
            fn($port) => Arr::get($port, 'country') === $country
        );
        if ($countryPorts->isEmpty()) {
            $port = $astroService->createPortByCountry($countryCode);
            $countryPorts->push($port);
        }
        $randomPort = $countryPorts->random();
        $astroService->changeIpOnPort(Arr::get($randomPort, 'id'));
        $proxy = $astroService->setProxy($randomPort);
        $ip = $astroService->checkMyIp($proxy);
        Arr::set($proxy, 'ip', $ip);

        return $proxy;
    }

    /**
     * @param Lead $lead
     * @param string $ip
     *
     * @return void
     */
    public function sendLead(Lead $lead, string $ip): void
    {
        $service = LeadServiceFactory::createService($lead->partner->external_id);
        $tmpLink = $service->send($lead->id, $ip);
        Http::get('localhost:4000', [
            'url' => $tmpLink,
        ]);
    }
}
