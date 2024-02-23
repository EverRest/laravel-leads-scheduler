<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Lead;
use App\Repositories\LeadRepository;
use App\Repositories\LeadResultRepository;
use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Spatie\LaravelData\Data;

abstract class LeadService
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
    }

    /**
     * @param Lead $lead
     *
     * @return string
     * @throws Exception
     */
    public function send(Lead $lead): string
    {
        $dto = $this->createDtoByLeadId($lead->id);
        $response = $this->sendRequest($dto, $lead);
        if ($response->failed()) {
            $this->astroService->deletePort($lead->leadProxy->external_id);
            Log::error($response->status() . " Partner $lead->partner_id is not available.");
        }
        $this->saveLeadResult($lead, $response);

        return $this->getAutoLoginUrl($response->json());
    }

    /**
     * @param Lead $lead
     * @param Response $response
     *
     * @return void
     */
    protected function SaveLeadResult(Lead $lead, Response $response): void
    {
        $this->leadResultRepository
            ->firstOrCreate(
                [
                    'lead_id' => $lead->id,
                    'status' => $response->status(),
                    'data' => $response->json(),
                ]
            );
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
     * @return string
     */
    protected abstract function getAutoLoginUrl(array $data): string;
}
