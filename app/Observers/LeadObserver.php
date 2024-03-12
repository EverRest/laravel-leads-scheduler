<?php

declare(strict_types=1);

namespace App\Observers;

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
        dispatch((new CreateLeadProxyJob($lead->id))->delay($scheduledTime->copy()->subMinutes()));
        dispatch((new SendLeadJob($lead->id))->delay($scheduledTime->copy()));
        dispatch((new DeleteLeadProxyJob($lead->id))->delay($scheduledTime->copy()->addMinutes(30)));
    }
}
