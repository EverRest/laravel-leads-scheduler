<?php
declare(strict_types=1);

namespace App\Services\Partner;

use App\Services\AffiliateKingzService;
use App\Services\Partner\IPartnerService;
use InvalidArgumentException;

final class PartnerServiceFactory
{
    /**
     * @param string $externalPartnerId
     *
     * @return IPartnerService
     */
    public static function createService(string $externalPartnerId): IPartnerService
    {
        return match ($externalPartnerId) {
            '1' => new AffiliateKingzService(),
            '2' => new CmAffsService(),
            '3' => new StarkIrevService(),
            default => throw new InvalidArgumentException('Invalid partner_id'),
        };
    }
}
