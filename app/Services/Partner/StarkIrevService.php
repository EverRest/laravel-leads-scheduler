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
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36',
        ])->withOptions([
            'verify' => false,
            'timeout' => 20000,
            'curl' => [
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_RETURNTRANSFER => true, // Added for better handling of the response
                CURLOPT_POSTFIELDS => http_build_query($dto->toArray()), // Send POST data as URL-encoded form data
            ],
            'debug' => true,
        ])
            ->post($url);
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
