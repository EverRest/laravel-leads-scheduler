<?php
declare(strict_types=1);

namespace App\Services;

use InvalidArgumentException;

class LeadServiceFactory
{
    /**
     * @param int $externalPartnerId
     *
     * @return ILeadService
     */
    public static function createService(string $externalPartnerId): ILeadService
    {
        return match ($externalPartnerId) {
            "1" => new AffiliateKingzService(),
            "2" => new CmAffsService(),
            "3" => new StarkIrevService(),
            default => throw new InvalidArgumentException('Invalid partner_id'),
        };
    }
}
