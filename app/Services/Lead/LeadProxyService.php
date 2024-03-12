<?php

declare(strict_types=1);

namespace App\Services\Lead;

use App\Models\Lead;
use App\Repositories\LeadRepository;
use App\Services\Proxy\AstroService;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

final class LeadProxyService
{
    /**
     * @param LeadRepository $leadRepository
     * @param AstroService $astroService
     */
    public function __construct(
        private readonly LeadRepository      $leadRepository,
        private readonly AstroService        $astroService,
    )
    {
    }

    /**
     * @param Lead $lead
     *
     * @return Model
     * @throws Exception
     */
    public function createProxyByLead(Lead $lead): Model
    {
        $country = $this->astroService->getCountryByISO2($lead->country);
        if (!$country) {
            $this->logCountryNotFoundError();
        }
        $port = $this->astroService->createPortByLead($country, $lead);
        $proxy = $this->astroService->setProxy($port);
        $ip = $this->astroService->newIp(Arr::get($port, 'id'));

        return $this->leadRepository->update($lead, [
            'ip' => $ip,
            'proxy_external_id' => Arr::get($proxy, 'id'),
            'host' => Arr::get($proxy, 'host'),
            'port' => Arr::get($proxy, 'port'),
            'protocol' => Arr::get($proxy, 'protocol'),
            'country_name' => $country,
        ]);
    }

    /**
     * @return void
     */
    private function logCountryNotFoundError(): void
    {
        $message = Carbon::now()->format('Y-m-d H:i:s') . ' Country not found in the proxy list.';
        Log::error($message);
    }
}
