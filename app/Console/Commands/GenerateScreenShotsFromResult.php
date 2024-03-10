<?php
declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\GenerateScreenShotFromLeadResultJob;
use App\Repositories\LeadRepository;
use Illuminate\Console\Command;

class GenerateScreenShotsFromResult extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pup:picture {leadId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @param LeadRepository $leadRepository
     * @return void
     */
    public function handle(
        LeadRepository      $leadRepository,
    ): void
    {
        $leadId = intval($this->argument('leadId'));
        $lead = $leadRepository->findOrFail($leadId);
        GenerateScreenShotFromLeadResultJob::dispatch($lead->leadResult);
    }
}

