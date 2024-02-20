<?php
declare(strict_types=1);

namespace App\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class FinalizeBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private readonly string $import)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            dd("{$this->import} " . Carbon::now()->format('Y-m-d H:i:s'));
        } catch (Exception $e) {
            $this->fail($e);
        }
    }
}
