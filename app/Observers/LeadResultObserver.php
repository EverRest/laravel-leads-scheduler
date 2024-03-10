<?php

declare(strict_types=1);

namespace App\Observers;

use App\Jobs\GenerateScreenShotFromLeadResultJob;
use App\Models\LeadResult;
use Illuminate\Support\Facades\Artisan;

class LeadResultObserver
{
    /**
     * Handle the LeadResult "created" event.
     */
    public function created(LeadResult $leadResult): void
    {
//        GenerateScreenShotFromLeadResultJob::dispatch($leadResult);
        Artisan::call('pup:picture', [
            'leadId' => $leadResult->lead_id
        ]);
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
