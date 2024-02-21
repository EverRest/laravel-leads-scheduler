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
        return $this->query()
            ->where('is_sent', false)
            ->whereBetween(
                'scheduled_at',
                [
                    Carbon::now()->subMonth(),
                    Carbon::now()->addMonth(),
//                    Carbon::now()->subMinute(),
//                    Carbon::now()->addMinute()
                ]
            )->get();
    }

    /**
     * @param string $import
     *
     * @return bool
     */
    public function getBatchResult(string $import): bool
    {
        return $this->query()
            ->where('import', $import)
            ->where('is_sent', false)
            ->exists();
    }

    /**
     * @param string $import
     *
     * @return Collection
     */
    public function getLeadsByImport(string $import): Collection
    {
        return $this->query()
            ->where('import', $import)
            ->get();
    }

    /**
     * @param int|string $partnerId
     * @param Carbon $fromDate
     * @param Carbon $toDate
     *
     * @return int
     */
    public function getScheduledLeadsCount(int|string $partnerId, Carbon $fromDate, Carbon $toDate): int
    {
        return $this->query()
            ->where('is_sent', false)
            ->where('partner_id', $partnerId)
            ->whereBetween('scheduled_at', [$fromDate, $toDate])
            ->count();
    }

    /**
     * @param int|string $partnerId
     * @param Carbon $fromDate
     * @param Carbon $toDate
     *
     * @return Collection
     */
    public function getScheduledLeadSlots(int|string $partnerId, Carbon $fromDate, Carbon $toDate): Collection
    {
        return $this->query()
            ->where('is_sent', false)
            ->where('partner_id', $partnerId)
            ->whereBetween('scheduled_at', [$fromDate, $toDate])
            ->pluck('scheduled_at');
    }
}
