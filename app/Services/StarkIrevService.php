<?php
declare(strict_types=1);

namespace App\Services;

use App\Dto\StarkIrevDto;
use App\Models\Lead;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Spatie\LaravelData\Data;

class StarkIrevService extends LeadService implements ILeadService
{
    /**
     * @param Lead $lead
     *
     * @return string
     */
    public function send(Lead $lead): string
    {
        $dto = $this->createDtoByLeadId($lead->id);
        $url = Config::get('services.startkirev.url');
        $response = Http::withHeaders([
            'Accept' => '*/*',
            'Accept-Encoding' => 'gzip, deflate',
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36',
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Host' => 'stark-ld.platform500.com',
        ])->withOptions([
            'proxy' => "http://{$lead->leadProxy->username}:{$lead->leadProxy->password}@{$lead->leadProxy->ip}:{$lead->leadProxy->port}",
            'verify' => false,
            'curl' => [
                CURLOPT_FOLLOWLOCATION => true,
            ],
            'debug' => true,
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

        return StarkIrevDto::from($lead->toArray());
    }
}
