<?php

declare(strict_types=1);

namespace App\Observers;

use App\Jobs\GenerateScreenShotJob;
use App\Models\LeadResult;

class LeadResultObserver
{
    /**
     * Handle the LeadResult "created" event.
     */
    public function created(LeadResult $leadResult): void
    {
        dispatch((new GenerateScreenShotJob($leadResult->lead->id))->delay(1));
    }

    /**
     * Handle the LeadResult "deleted" event.
     */
    public function deleted(LeadResult $leadResult): void
    {
        $leadResult->lead->leadRedirect()->delete();
    }

    /**
     * Handle the LeadResult "restored" event.
     */
    public function restored(LeadResult $leadResult): void
    {
        $leadResult->lead->leadRedirect()->restore();
    }
}
