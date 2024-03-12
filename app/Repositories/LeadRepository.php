<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\Lead;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

final class LeadRepository extends Repository
{
    /**
     * @var string $model
     */
    protected string $model = Lead::class;

    /**
     * @var int $default_limit
     */
    protected int $default_limit = 1000;

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
//                    Carbon::now()->subMonth(),
//                    Carbon::now()->addMonth(),
                    Carbon::now()->subMinute(),
                    Carbon::now()->addMinute()
                ]
            )->get();
    }

    /**
     * @return Collection
     */
    public function getLeadsWithoutProxy(): Collection
    {
        return $this->query()
            ->where('is_sent', false)
            ->where('scheduled_at', '=<', Carbon::now()->addMinutes(1)->toDateTimeString())
            ->where('scheduled_at', '>', Carbon::now()->addMinutes(5    )->toDateTimeString())
            ->whereDoesntHave('leadProxy')->get();
    }

    /**
     * @return Collection
     */
    public function getTodayLeadsProxy(): Collection
    {
        return $this->query()
            ->where('is_sent', false)
            ->where('scheduled_at', '>=', Carbon::today()->toDateTimeString())
            ->where('scheduled_at', '<', Carbon::tomorrow()->toDateTimeString())
            ->get();
    }

    /**
     * @return Collection
     */
    public function getSentLeads(): Collection
    {
        return $this->query()
//            ->where('scheduled_at', '<', Carbon::now()->toDateTimeString())
            ->where('scheduled_at', '>=', Carbon::now()->subMinutes(1)->toDateTimeString())
            ->get();
    }

    /**
     * @return Collection
     */
    public function getLeadsWithRedirects(): Collection
    {
        return $this->query()
            ->where('is_sent', true)
            ->where('scheduled_at', '>=', Carbon::today()->toDateTimeString())
            ->get();
    }

    /**
     * @param string $import
     *
     * @return bool
     */
    public function getBatchResult(string $import): bool
    {
        return !$this->query()
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
            ->where('partner_id', $partnerId)
            ->whereBetween('scheduled_at', [$fromDate, $toDate])
            ->pluck('scheduled_at');
    }
}
