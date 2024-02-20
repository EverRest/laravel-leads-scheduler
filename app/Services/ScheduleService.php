<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Partner;
use App\Repositories\LeadRepository;
use App\Repositories\PartnerRepository;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use RuntimeException;

class ScheduleService
{
    private const DEFAULT_TIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * ScheduleService constructor.
     *
     * @param PartnerRepository $partnerRepository
     * @param LeadRepository    $leadRepository
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
     * @throws Exception
     */
    public function scheduleLeads(
        array      $importedLeads,
        int|string $partnerId,
        Carbon     $fromDate,
        Carbon     $toDate
    ): Collection
    {
        $leadModels = Collection::make();
        /**
         * @var Partner $partner
         */
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
        $scheduledLeadsCount = $this->leadRepository
            ->getScheduledLeadsCount($partnerId, $fromDate, $toDate);

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
            'import' => Arr::get($importedLead, 'import') . '-' . Str::random(10)
        ];

        return $this->leadRepository
            ->store($attributes);
    }

    /**
     * @param string|int $partnerId
     * @param Carbon $fromDate
     * @param Carbon $toDate
     * @param int $minInterval
     *
     * @return Carbon
     */
    public function findFreeSlot(string|int $partnerId, Carbon $fromDate, Carbon $toDate, int $minInterval = 5): Carbon
    {
        $timeInterval = $minInterval;
        $minDifference = $minInterval + 1;
        $diffInMinutes = $toDate->diffInMinutes($fromDate);
        $numberOfSlots = intval($diffInMinutes / $timeInterval);
        $availableSlots = Collection::make();
        for ($i = 0; $i < $numberOfSlots; $i++) {
            $randomOffset = rand(0, $minDifference - 1);
            $slot = $fromDate->copy()->addMinutes(($i * $timeInterval) + $randomOffset);
            $availableSlots->push($slot);
        }
        $scheduledSlots = $this->leadRepository->getScheduledLeadSlots($partnerId, $fromDate, $toDate);
        $shapedSlots = Collection::make();
        foreach ($scheduledSlots as $scheduledSlot) {
            $scheduledTime = Carbon::parse($scheduledSlot);
            for ($i = 0; $i < $timeInterval; $i++) {
                $closedPreviousSlot = $scheduledTime->copy()->subMinutes($i);
                $closedNextSlot = $scheduledTime->copy()->addMinutes($i);
                $shapedSlots->push($closedPreviousSlot->format(self::DEFAULT_TIME_FORMAT));
                $shapedSlots->push($closedNextSlot->format(self::DEFAULT_TIME_FORMAT));
            }
        }
        $scheduledSlots = $scheduledSlots->merge($shapedSlots)->unique();
        $availableSlots = $availableSlots->diff($scheduledSlots);
        if ($availableSlots->isEmpty()) {
            throw new RuntimeException('No available slots within the specified range');
        }

        return $availableSlots->random();
    }
}
