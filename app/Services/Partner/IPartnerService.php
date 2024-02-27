<?php
declare(strict_types=1);

namespace App\Services\Partner;

use App\Models\Lead;

interface IPartnerService
{
    /**
     * @param Lead $lead
     *
     * @return string
     */
    public function send(Lead $lead):string;
}
