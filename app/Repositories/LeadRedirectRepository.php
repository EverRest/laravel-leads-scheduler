<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\LeadRedirect;

class LeadRedirectRepository extends Repository
{
    /**
     * @var string $model
     */
    protected string $model = LeadRedirect::class;
}
