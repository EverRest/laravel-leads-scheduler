<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\LeadResult;

class LeadResultRepository extends Repository
{
    /**
     * @var string $model
     */
    protected string $model = LeadResult::class;
}
