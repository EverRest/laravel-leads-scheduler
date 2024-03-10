<?php
declare(strict_types=1);

namespace App\Services\Schedule;

use App\Jobs\CloseBatchJob;
use App\Jobs\CreateLeadProxyJob;
use App\Jobs\GenerateScreenShotJob;
use App\Jobs\SendLeadJob;
use App\Models\Partner;
use App\Repositories\LeadRepository;
use App\Repositories\PartnerRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use RuntimeException;

final class ScheduleService
{
    private const DEFAULT_TIME_FORMAT = 'Y-m-d H:i:s';
    private const MIN_TIME_INTERVAL_BETWEEN_LEADS = 5;

    /**
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
            $scheduledTime = Carbon::parse($leadModel->scheduled_at);
            dispatch((new CreateLeadProxyJob($leadModel->id))->delay($scheduledTime->copy()->subMinutes(3)));
            dispatch((new SendLeadJob($leadModel->id))->delay($scheduledTime->copy()));
            dispatch((new GenerateScreenShotJob($leadModel->id))->delay($scheduledTime->copy()->addMinutes()));
//            dispatch((new DeleteLeadProxyJob($leadModel->id))->delay($scheduledTime->copy()->addMinutes()));
        }
        dispatch((new CloseBatchJob($leadModel->id))
            ->delay($toDate->copy()->addMinutes(10)));
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

        return intval($freeMinutes / self::MIN_TIME_INTERVAL_BETWEEN_LEADS - $scheduledLeadsCount);
    }

    /**
     * @param array $importedLead
     * @param Partner $partner
     * @param Carbon $fromDate
     * @param Carbon $toDate
     *
     * @return Model
     */
    private function scheduleLead(array $importedLead, Partner $partner, Carbon $fromDate, Carbon $toDate): Model
    {
        $freeSlot = $this->findFreeSlot($partner->id, $fromDate, $toDate);
        $attributes = $this->getLeadAttributes($importedLead, $partner, $freeSlot);

        return $this->leadRepository
            ->store($attributes);
    }

    /**
     * @param string|int $partnerId
     * @param Carbon $fromDate
     * @param Carbon $toDate
     *
     * @return Carbon
     */
    private function findFreeSlot(string|int $partnerId, Carbon $fromDate, Carbon $toDate): Carbon
    {
        $availableSlots = $this->findFreeSlots($partnerId, $fromDate, $toDate);
        if ($availableSlots->isEmpty()) {
            throw new RuntimeException('No available slots within the specified range');
        }

        return $availableSlots->random();
    }

    /**
     * @param Carbon $fromDate
     * @param Carbon $toDate
     *
     * @return Collection
     */
    private function generateAvailableSlots(Carbon $fromDate, Carbon $toDate): Collection
    {
        $timeInterval = self::MIN_TIME_INTERVAL_BETWEEN_LEADS;
        $minDifference = self::MIN_TIME_INTERVAL_BETWEEN_LEADS + 1;
        $diffInMinutes = $toDate->diffInMinutes($fromDate);
        $numberOfSlots = intval($diffInMinutes / $timeInterval);
        $availableSlots = Collection::make();
        for ($i = 0; $i < $numberOfSlots; $i++) {
            $randomOffset = rand(0, $minDifference - 1);
            $slot = $fromDate->copy()->addMinutes(($i * $timeInterval) + $randomOffset);
            $availableSlots->push($slot);
        }

        return $availableSlots;
    }

    /**
     * @param Collection $scheduledSlots
     *
     * @return Collection
     */
    private function shapeScheduledSlots(Collection $scheduledSlots): Collection
    {
        $timeInterval = self::MIN_TIME_INTERVAL_BETWEEN_LEADS;
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

        return $shapedSlots;
    }

    /**
     * @param string|int $partnerId
     * @param Carbon $fromDate
     * @param Carbon $toDate
     *
     * @return Collection
     */
    private function findFreeSlots(string|int $partnerId, Carbon $fromDate, Carbon $toDate): Collection
    {
        $availableSlots = $this->generateAvailableSlots($fromDate, $toDate);
        $scheduledSlots = $this->leadRepository->getScheduledLeadSlots($partnerId, $fromDate, $toDate);
        $shapedSlots = $this->shapeScheduledSlots($scheduledSlots);
        $scheduledSlots = $scheduledSlots->merge($shapedSlots)->unique();

        return $availableSlots->diff($scheduledSlots);
    }

    /**
     * @param array $importedLead
     * @param Partner $partner
     * @param Carbon $freeSlot
     *
     * @return array
     */
    private function getLeadAttributes(array $importedLead, Partner $partner, Carbon $freeSlot): array
    {
        return [
            'partner_id' => $partner->id,
            'scheduled_at' => $freeSlot,
            'first_name' => Arr::get($importedLead, 'first_name'),
            'last_name' => Arr::get($importedLead, 'last_name'),
            'email' => Arr::get($importedLead, 'email'),
            'phone' => Arr::get($importedLead, 'phone'),
            'phone_code' => Arr::get($importedLead, 'phone_phoneCode'),
            'password' => Arr::get($importedLead, 'password'),
            'country' => Arr::get($importedLead, 'ip_data.country'),
            'import' => $partner->id . '-' . Carbon::now()->format(self::DEFAULT_TIME_FORMAT),
        ];
    }
}
