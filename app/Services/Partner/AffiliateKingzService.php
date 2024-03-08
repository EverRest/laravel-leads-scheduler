<?php
declare(strict_types=1);

namespace App\Services\Partner;

use App\Dto\AffiliateKingzDto;
use App\Models\Lead;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Spatie\LaravelData\Data;

final class AffiliateKingzService extends PartnerService implements IPartnerService
{
    /**
     * @param int $leadId
     *
     * @return Data
     */
    protected function createDtoByLeadId(int $leadId): Data
    {
        $lead = $this->leadRepository->findOrFail($leadId);

        return AffiliateKingzDto::from($lead->toArray());
    }

    /**
     * @param Data $dto
     * @param Lead $lead
     *
     * @return Response
     */
    protected function sendRequest(Data $dto, Lead $lead): Response
    {
        $url = Config::get('services.affiliatekingz.url');
        $headers = $this->getHeaders();
        $options = $this->getOptions($lead);

        $data = $this->getData($dto, $lead);

        return Http::withHeaders($headers)
            ->withOptions($options)
            ->asForm()
            ->post($url, $data);
    }

    /**
     * @param array $data
     *
     * @return string
     */
    protected function getAutoLoginUrl(array $data): string
    {
        return Arr::get($data, 'extras.redirect.url', '');
    }

    /**
     * @return string[]
     */
    private function getHeaders(): array
    {
        return [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Accept' => '*/*',
            'Accept-Encoding' => 'gzip, deflate',
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36',
        ];
    }

    /**
     * @param Lead $lead
     *
     * @return array
     */
    private function getOptions(Lead $lead): array
    {
        return [
//            'proxy' => "http://{$lead->leadProxy->username}:{$lead->leadProxy->password}@{$lead->leadProxy->host}:{$lead->leadProxy->port}",
            'verify' => false,
            'timeout' => 20000,
            'curl' => [CURLOPT_FOLLOWLOCATION => true,],
            'debug' => true,
        ];
    }

    /**
     * @param Data $dto
     * @param Lead $lead
     *
     * @return array
     */
    private function getData(Data $dto, Lead $lead): array
    {
        return [...$dto->toArray(), '_ip' => $lead->leadProxy->ip,];
    }
}
