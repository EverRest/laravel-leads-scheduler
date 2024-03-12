<?php
declare(strict_types=1);

namespace App\Services\Partner;

use App\Dto\StarkIrevDto;
use App\Models\Lead;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Spatie\LaravelData\Data;

final class StarkIrevService extends PartnerService implements IPartnerService
{
    /**
     * @param int $leadId
     *
     * @return Data
     */
    protected function createDtoByLeadId(int $leadId): Data
    {
        $lead = $this->leadRepository->findOrFail($leadId);

        return StarkIrevDto::from($lead->toArray());
    }

    /**
     * @param Data $dto
     * @param Lead $lead
     *
     * @return Response
     */
    protected function sendRequest(Data $dto, Lead $lead): Response
    {
        $url = Config::get('services.startkirev.url');

        return Http::withHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded',
            'x-api-key' => 'fdb03b91-7ffc-4c9b-84bc-5cae4bacd446',
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36',
            'Accept' => '*/*',
            'Content-Length' => '364',
        ])->withOptions([
            'proxy' => "http://{$lead->first_name}:{$lead->password}@{$lead->lhost}:{$lead->port}",
            'verify' => false,
            'timeout' => 20000,
            'curl' => [
                CURLOPT_FOLLOWLOCATION => true,
            ],
            'debug' => true,
        ])
            ->post($url, $dto->toArray());
    }

    /**
     * @param array $data
     *
     * @return string|null
     */
        protected function getAutoLoginUrl(array $data = []): ?string
    {
        return Arr::get($data, 'auto_login_url', '');
    }
}
