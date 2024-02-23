<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Lead;

interface ILeadService
{
    /**
     * @param Lead $lead
     *
     * @return string
     */
    public function send(Lead $lead):string;
}
