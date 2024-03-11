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
            'Accept' => '*/*',
            'Content-Length' => '364',
        ])
            ->post($url, $dto->toArray());
    }

    /**
     * @param array $data
     *
     * @return string
     */
        protected function getAutoLoginUrl(array $data): ?string
    {
        return Arr::get($data, 'auto_login_url', '');
    }
}
