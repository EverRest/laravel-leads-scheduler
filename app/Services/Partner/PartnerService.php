<?php
declare(strict_types=1);

namespace App\Services\Partner;

use App\Models\Lead;
use App\Repositories\LeadProxyRepository;
use App\Repositories\LeadRepository;
use App\Repositories\LeadResultRepository;
use App\Services\Proxy\AstroService;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Spatie\LaravelData\Data;

abstract class PartnerService
{
    /**
     * @var LeadRepository $leadRepository
     */
    protected LeadRepository $leadRepository;

    /**
     * @var LeadResultRepository $leadResultRepository
     */
    protected LeadResultRepository $leadResultRepository;

    /**
     * @var LeadProxyRepository $leadProxyRepository
     */
    protected LeadProxyRepository $leadProxyRepository;

    /**
     * @var AstroService $astroService
     */
    protected AstroService $astroService;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->leadRepository = App::make(LeadRepository::class);
        $this->leadResultRepository = App::make(LeadResultRepository::class);
        $this->astroService = App::make(AstroService::class);
        $this->leadProxyRepository = App::make(LeadProxyRepository::class);
    }

    /**
     * @param Lead $lead
     *
     * @return string|null
     */
    public function send(Lead $lead): ?string
    {
        $dto = $this->createDtoByLeadId($lead->id);
        $response = $this->sendRequest($dto, $lead);
        $this->saveLeadResult($lead, $response);
        if ($response->failed()) {
            Log::error($response->status() . " Partner $lead->partner_id is not available.");
        }

        return $this->getAutoLoginUrl($response->json() ?? []);
    }

    /**
     * @param Lead $lead
     * @param Response $response
     *
     * @return void
     */
    protected function SaveLeadResult(Lead $lead, Response $response): void
    {
        $result = $response->json();
        $this->leadRepository
            ->update($lead, [
                'status' => $response->status(),
                'data' => $result,
                'link' =>  $this->getAutoLoginUrl($result??[]),
            ]);
        $this->leadResultRepository
            ->firstOrCreate([
                'lead_id' => $lead->id,
                'status' => $response->status(),
                'data' => $response->json(),
            ]);
    }

    protected abstract function sendRequest(Data $dto, Lead $lead): Response;

    /**
     * @param int $leadId
     *
     * @return Data
     */
    protected abstract function createDtoByLeadId(int $leadId): Data;

    /**
     * @param array $data
     *
     * @return string|null
     */
    protected abstract function getAutoLoginUrl(array $data = []): ?string;
}
