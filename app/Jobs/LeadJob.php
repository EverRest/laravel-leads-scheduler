<?php
declare(strict_types=1);

namespace App\Jobs;

use App\Models\Lead;
use App\Repositories\LeadRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

class LeadJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Lead $lead
     */
    protected Lead $lead;

    /**
     * @var int $timeout
     */
    protected int $timeout = 600;

    /**
     * Create a new job instance.
     */
    public function __construct(protected int $leadId)
    {
        $repository = App::make(LeadRepository::class);
        $this->lead = $repository->findOrFail($leadId);
    }
}
