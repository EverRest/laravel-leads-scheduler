<?php
declare(strict_types=1);

namespace App\Services;

use InvalidArgumentException;

class LeadServiceFactory
{
    /**
     * @param string $externalPartnerId
     *
     * @return IPartnerService
     */
    public static function createService(string $externalPartnerId): IPartnerService
    {
        return match ($externalPartnerId) {
            "1" => new AffiliateKingzService(),
            "2" => new CmAffsService(),
            "3" => new StarkIrevService(),
            default => throw new InvalidArgumentException('Invalid partner_id'),
        };
    }
}
