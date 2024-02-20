<?php
declare(strict_types=1);

namespace App\Rules;

use App\Services\ScheduleService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Translation\PotentiallyTranslatedString;

class LeadPartnerRule implements ValidationRule
{
    /**
     * @param string|int $partnerId
     * @param string $from
     * @param string $to
     */
    public function __construct(
        private readonly string|int $partnerId,
        private readonly string $from,
        private readonly string $to
    )
    {
    }

    /**
     * Run the validation rule.
     *
     * @param Closure(string): PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        /** @var ScheduleService $service */
        $service = App::make(ScheduleService::class);
        $count = $service->countFreeSlots($this->partnerId, Carbon::parse($this->from), Carbon::parse($this->to));
        if ($count <= count($value)) {
            $fail("The number of leads exceeds the number of free slots for the partner.");
        }
    }
}
