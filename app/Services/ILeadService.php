<?php
declare(strict_types=1);

namespace App\Services;

use Spatie\LaravelData\Data;

interface ILeadService
{
    /**
     * @param int $leadId
     * @param string $ip
     *
     * @return mixed
     */
    public function send(int $leadId, string $ip):string;

    /**
     * @param int $leadId
     *
     * @return Data
     */
    public function createDtoByLeadId(int $leadId):Data;
}
