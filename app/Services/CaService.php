<?php
declare(strict_types=1);

namespace App\Services;

use App\Dto\CaDto;
use App\Models\Lead;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Spatie\LaravelData\Data;

class CaService extends LeadService implements ILeadService
{
    /**
     * @param Lead $lead
     *
     * @return string
     * @throws Exception
     */
    public function send(Lead $lead): string
    {
        $dto = $this->createDtoByLeadId($lead->id);
        $url = Config::get('services.cmaffs.url');
        $response = Http::withOptions([
            'proxy' => "http://{$lead->leadProxy->username}:{$lead->leadProxy->password}@{$lead->leadProxy->ip}:{$lead->leadProxy->port}",
            'verify' => false,
            'curl' => [
                CURLOPT_FOLLOWLOCATION => true,
            ],
        ])->withHeaders([
            'Accept' => '*/*',
            'Accept-Encoding' => 'gzip, deflate',
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36',
            'x-api-key' => '426ab522-a627-4d46-a792-7ac4ec68ab08',
            'Content-Type' => 'application/x-www-form-urlencoded',
        ])
            ->asForm()
            ->post($url, [...$dto->toArray(), 'ip' => $lead->leadProxy->ip,]);
        $this->leadResultRepository
            ->firstOrCreate(
                [
                    'lead_id' => $lead->id,
                    'status' => $response->status(),
                    'data' => $response->json(),
                ]
            );
        if ($response->failed()) {
            Log::error($response->status() . ' Partner is not available.');
        }
        $json = $response->json();

        return Arr::get($json, 'auto_login_url', '');
    }

    /**
     * @param int $leadId
     *
     * @return Data
     */
    public function createDtoByLeadId(int $leadId): Data
    {
        $lead = $this->leadRepository->findOrFail($leadId);

        return CaDto::from($lead->toArray());
    }
}
