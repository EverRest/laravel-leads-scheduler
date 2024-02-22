<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Lead;
use Spatie\LaravelData\Data;

interface ILeadService
{
    /**
     * @param Lead $lead
     *
     * @return string
     */
    public function send(Lead $lead):string;

    /**
     * @param int $leadId
     *
     * @return Data
     */
    public function createDtoByLeadId(int $leadId):Data;
}
