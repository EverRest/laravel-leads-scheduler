<?php
declare(strict_types=1);

namespace App\Services\Partner;

use App\Dto\CmAffsDto;
use App\Models\Lead;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Spatie\LaravelData\Data;

final class CmAffsService extends PartnerService implements IPartnerService
{
    /**
     * @param int $leadId
     *
     * @return Data
     */
    protected function createDtoByLeadId(int $leadId): Data
    {
        $lead = $this->leadRepository->findOrFail($leadId);

        return CmAffsDto::from($lead->toArray());
    }

    /**
     * @param Data $dto
     * @param Lead $lead
     *
     * @return Response
     */
    protected function sendRequest(Data $dto, Lead $lead): Response
    {
        $url = Config::get('services.cmaffs.url');

        return Http::withHeaders([
            'Content-Length' => '239',
            'Accept' => '*/*',
            'Accept-Encoding' => 'gzip, deflate',
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36',
            'Host' => 'api.lead2trk.online',
            'x-api-key' => '426ab522-a627-4d46-a792-7ac4ec68ab08',
            'Content-Type' => 'application/x-www-form-urlencoded',
        ])->withOptions([
//            'proxy' => "http://{$lead->leadProxy->username}:{$lead->leadProxy->password}@{$lead->leadProxy->host}:{$lead->leadProxy->port}",
            'verify' => false,
            'timeout' => 20000,
            'curl' => [
                CURLOPT_FOLLOWLOCATION => true,
            ],
            'debug' => true,
        ])
            ->asForm()
            ->post($url, [...$dto->toArray(), 'ip' => $lead->leadProxy->ip,]);
    }

    /**
     * @param array $data
     *
     * @return string
     */
    protected function getAutoLoginUrl(array $data): string
    {
        return Arr::get($data, 'data.redirect_url', '');
    }
}
