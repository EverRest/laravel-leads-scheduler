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
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class CreateProxy implements ShouldQueue
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
     * @param AstroService $astroService
     * @param LeadRepository $leadRepository
     * @param LeadProxyRepository $leadProxyRepository
     *
     * @return void
     */
    public function handle(
        AstroService        $astroService,
        LeadRepository      $leadRepository,
        LeadProxyRepository $leadProxyRepository,
    ): void
    {
        try {
            /** @var Lead $lead */
            $lead = $leadRepository->findOrFail($this->leadId);
            $this->createPort($astroService, $leadProxyRepository, $lead);
            Log::info(get_class($this) . ': Port created for lead ' . $lead->id . '.');
        } catch (Exception $e) {
            $this->fail($e);
        }
    }

    /**
     * @param AstroService $astroService
     * @param LeadProxyRepository $leadProxyRepository
     * @param Lead $lead
     *
     * @return array
     * @throws Exception
     */
    private function createPort(AstroService $astroService, LeadProxyRepository $leadProxyRepository, Lead $lead): array
    {
        $country = $astroService->getCountryByISO2($lead->country);
        if (!$country) {
            Log::error(Carbon::now()->format('Y-m-d H:i:s') . ' Country not found in the proxy list.');
        }
        $port = $astroService->createPortByLead($country, $lead);
        $proxy = $astroService->setProxy($port);
        $ip = $astroService->newIp(Arr::get($port, 'id'));
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
