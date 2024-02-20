<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Lead;
use App\Models\Partner;
use App\Repositories\LeadRepository;
use App\Repositories\PartnerRepository;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use RuntimeException;

class ScheduleService
{
    /**
     * ScheduleService constructor.
     *
     * @param PartnerRepository $partnerRepository
     * @param LeadRepository $leadRepository
     */
    public function __construct(
        private readonly PartnerRepository $partnerRepository,
        private readonly LeadRepository    $leadRepository,
    )
    {
    }

    /**
     * @param array $importedLeads
     * @param int|string $partnerId
     * @param Carbon $fromDate
     * @param Carbon $toDate
     *
     * @return Collection
     */
    public function scheduleLeads(
        array      $importedLeads,
        int|string $partnerId,
        Carbon     $fromDate,
        Carbon     $toDate
    ): Collection
    {
        $leadModels = Collection::make();
        $partner = $this->partnerRepository->findByExternalId($partnerId);
        foreach ($importedLeads as $importedLead) {
            $leadModel = $this->scheduleLead($importedLead, $partner, $fromDate, $toDate);
            $leadModels->push($leadModel);
        }

        return $leadModels;
    }

    /**
     * @param string|int $partnerId
     * @param Carbon $fromDate
     * @param Carbon $toDate
     *
     * @return int
     */
    public function countFreeSlots(string|int $partnerId, Carbon $fromDate, Carbon $toDate): int
    {
        $freeMinutes = $toDate->diffInMinutes($fromDate);
        $scheduledLeadsCount = $this->leadRepository->query()
            ->where('is_sent', false)
            ->where('partner_id', $partnerId)
            ->whereBetween('scheduled_at', [$fromDate, $toDate])
            ->count();

        return intval($freeMinutes / 5 - $scheduledLeadsCount);
    }

    /**
     * @param array $importedLead
     * @param Partner $partner
     * @param Carbon $fromDate
     * @param Carbon $toDate
     *
     * @return Model
     * @throws Exception
     */
    private function scheduleLead(array $importedLead, Partner $partner, Carbon $fromDate, Carbon $toDate): Model
    {
        $freeSlot = $this->findFreeSlot($partner->id, $fromDate, $toDate);
        $attributes = [
            'partner_id' => $partner->id,
            'scheduled_at' => $freeSlot,
            'first_name' => Arr::get($importedLead, 'first_name'),
            'last_name' => Arr::get($importedLead, 'last_name'),
            'email' => Arr::get($importedLead, 'email'),
            'offer_url' => Arr::get($importedLead, 'offerUrl'),
            'phone' => Arr::get($importedLead, 'phone'),
            'password' => Arr::get($importedLead, 'password'),
            'country' => Arr::get($importedLead, 'ip_data.country'),
            'ip' => Arr::get($importedLead, 'ip_data.ip'),
        ];

        return $this->leadRepository
            ->firstOrCreate($attributes);
    }

    /**
     * @param string|int $partnerId
     * @param Carbon $fromDate
     * @param Carbon $toDate
     *
     * @return Carbon
     */
    public function findFreeSlot(string|int $partnerId, Carbon $fromDate, Carbon $toDate): Carbon
    {
        $timeInterval = 5;
        $diffInMinutes = $toDate->diffInMinutes($fromDate);
        $numberOfSlots = intval($diffInMinutes / $timeInterval);
        $allSlots = collect(range(0, $numberOfSlots - 1))
            ->map(function ($slotIndex) use ($fromDate, $timeInterval) {
                return $fromDate->copy()->addMinutes($slotIndex * $timeInterval);
            });
        $scheduledSlots = $this->leadRepository->query()
            ->where('is_sent', false)
            ->where('partner_id', $partnerId)
            ->whereBetween('scheduled_at', [$fromDate, $toDate])
            ->pluck('scheduled_at');
        $availableSlots = $allSlots->diff($scheduledSlots);
        if ($availableSlots->isEmpty()) {
            throw new RuntimeException('No available slots within the specified range');
        }

        return $availableSlots->random();
    }
}
