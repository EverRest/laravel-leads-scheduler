<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\Lead;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class LeadRepository extends Repository
{
    /**
     * @var string $model
     */
    protected string $model = Lead::class;

    /**
     * @return Collection
     */
    public function getLeadsToSend(): Collection
    {
        return $this->query()->where('is_sent', false)
            ->whereBetween(
                'send_at',
                [
                    Carbon::now()->subMonth(),
                    Carbon::now()->addMonth(),
//                    Carbon::now()->subMinute(),
//                    Carbon::now()->addMinute()
                ]
            )->get();
    }
}
