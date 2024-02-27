<?php
declare(strict_types=1);

namespace App\Console\Commands;

use App\Dto\AstroPortDto;
use App\Models\LeadProxy;
use App\Services\Proxy\AstroService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class DeleteProxy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lead:delete-proxy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(AstroService $astroService): void
    {
        $proxies = $astroService->getAvailablePorts();
        $astroProxyDtoCollection = AstroPortDto::collect($proxies);
        $proxyIdCollection = LeadProxy::withTrashed()
            ->whereDate('created_at', '>', Carbon::now()->subDay())
            ->pluck('external_id');
        $astroProxyDtoCollection->filter(
            fn(AstroPortDto $astroPortDto) => $proxyIdCollection->contains($astroPortDto->external_id)
        )->each(
            fn(AstroPortDto $astroPortDto) => $astroService->deletePort($astroPortDto->external_id)
        );
        LeadProxy::whereIn('external_id', $proxyIdCollection->toArray())->delete();
    }
}
