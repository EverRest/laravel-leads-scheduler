<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\LeadProxy;

class LeadProxyRepository extends Repository
{
    /**
     * @var string $model
     */
    protected string $model = LeadProxy::class;
}
