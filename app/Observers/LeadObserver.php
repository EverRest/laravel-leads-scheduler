<?php

namespace App\Observers;

use App\Jobs\CloseBatchJob;
use App\Jobs\CreateLeadProxyJob;
use App\Jobs\DeleteLeadProxyJob;
use App\Jobs\SendLeadJob;
use App\Models\Lead;
use Illuminate\Support\Carbon;

class LeadObserver
{
    /**
     * Handle the Lead "created" event.
     */
    public function created(Lead $lead): void
    {
        $scheduledTime = Carbon::parse($lead->scheduled_at);
        dispatch((new CreateLeadProxyJob($lead->id))->delay($scheduledTime->copy()->subMinutes(3)));
        dispatch((new SendLeadJob($lead->id))->delay($scheduledTime->copy()));
//            dispatch((new GenerateScreenShotJob($leadModel->id))->delay($scheduledTime->copy()->addMinutes()));
//            dispatch((new DeleteLeadProxyJob($lead->id))->delay($scheduledTime->copy()->addMinutes(5)));
//        dispatch((new CloseBatchJob($lead->id))
//            ->delay($scheduledTime->copy()->addMinutes(5)));
    }

    /**
     * Handle the Lead "updated" event.
     */
    public function updated(Lead $lead): void
    {
        //
    }

    /**
     * Handle the Lead "deleted" event.
     */
    public function deleted(Lead $lead): void
    {
        //
    }

    /**
     * Handle the Lead "restored" event.
     */
    public function restored(Lead $lead): void
    {
        //
    }

    /**
     * Handle the Lead "force deleted" event.
     */
    public function forceDeleted(Lead $lead): void
    {
        //
    }
}
