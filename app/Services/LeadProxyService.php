<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Lead;
use App\Models\LeadProxy;
use App\Repositories\LeadProxyRepository;
use App\Repositories\LeadRepository;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Throwable;

class LeadProxyService
{
    /**
     * @param LeadRepository $leadRepository
     * @param LeadProxyRepository $leadProxyRepository
     * @param AstroService $astroService
     */
    public function __construct(
        private readonly LeadRepository $leadRepository,
        private readonly LeadProxyRepository $leadProxyRepository,
        private readonly AstroService $astroService,
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
            $message = Carbon::now()->format('Y-m-d H:i:s') . ' Country not found in the proxy list.';
            Log::error($message);
        }
        $port = $this->astroService->createPortByLead($country, $lead);
        $proxy = $this->astroService->setProxy($port);
        sleep(5);
        $ip = $this->astroService->newIp(Arr::get($port, 'id'));
        $leadProxyAttributes = [
            'lead_id' => $lead->id,
            'ip' => $ip,
            'external_id' => Arr::get($proxy, 'id'),
            'port' => Arr::get($proxy, 'port'),
            'protocol' => Arr::get($proxy, 'protocol'),
            'username' => Arr::get($proxy, 'username'),
            'password' => Arr::get($proxy, 'password'),
            'host' => Arr::get($proxy, 'host'),
            'country' => $country,
        ];
        Log::info(get_class($this) . ': Port created for lead ' . $lead->id . '.');

        return $this->leadProxyRepository->firstOrCreate($leadProxyAttributes);
    }

    /**
     * @param Lead $lead
     *
     * @return LeadProxy
     * @throws Throwable
     */
    public function deleteProxyByLead(Lead $lead): LeadProxy
    {
        $this->leadRepository->patch($lead, 'is_sent', true);
        if($lead->leadRedirect?->file) {
            $this->astroService->deletePort($lead->leadProxy->external_id);
        }
        $leadProxy = $lead->leadProxy;
        $this->leadProxyRepository->query()->where('lead_id', $lead->id)->delete();
        Log::info(get_class($this) . ': Port deleted for lead ' . $lead->id . '.');

        return $leadProxy;
    }
}
