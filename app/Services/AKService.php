<?php
declare(strict_types=1);

namespace App\Services;

class AKService implements ILeadService
{
    /**
     * @param int $leadId
     *
     * @return mixed
     */
    public function send(int $leadId): string
    {
        return '';
    }
}
