<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\LeadResult;
use App\Services\Lead\LeadRedirectService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;

class LeadResultObserver
{
    /**
     * Handle the LeadResult "created" event.
     */
    public function created(LeadResult $leadResult): void
    {
        $leadRedirect = Arr::get($leadResult->toArray(), $leadResult->lead->redirectLinkKey);
        App::make(LeadRedirectService::class)->storeRedirectLink($leadResult->lead, $leadRedirect);
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
