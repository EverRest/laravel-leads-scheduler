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
    public static function createService(int $externalPartnerId): ILeadService
    {
        return match ($externalPartnerId) {
            1 => new AKService(),
            2 => new CAService(),
            3 => new SIService(),
            default => throw new InvalidArgumentException('Invalid partner_id'),
        };
    }
}
