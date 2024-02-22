<?php
declare(strict_types=1);

namespace App\Services;

use App\Dto\CaDto;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Spatie\LaravelData\Data;

class CaService extends LeadService implements ILeadService
{
    /**
     * @param int $leadId
     * @param string $ip
     *
     * @return string
     * @throws Exception
     */
    public function send(int $leadId, string $ip): string
    {
        $dto = $this->createDtoByLeadId($leadId);
        $url = Config::get('services.cmaffs.url');
        $response = Http::withHeaders([
            'Accept' => '*/*',
            'Accept-Encoding' => 'gzip, deflate',
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36',
            'x-api-key' => '426ab522-a627-4d46-a792-7ac4ec68ab08',
            'Content-Type' => 'application/x-www-form-urlencoded',
        ])
            ->asForm()
            ->post($url, [...$dto->toArray(), 'ip' => $ip,]);
        $this->leadResultRepository
            ->firstOrCreate(
                [
                    'lead_id' => $leadId,
                    'status' => $response->status(),
                    'data' => $response->json(),
                ]
            );
        if ($response->failed()) {
            throw new Exception('Partner is not available.');
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
