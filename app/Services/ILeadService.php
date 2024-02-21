<?php
declare(strict_types=1);

namespace App\Services;

interface ILeadService
{
    /**
     * @param int $leadId
     *
     * @return mixed
     */
    public function send(int $leadId):string;
}
