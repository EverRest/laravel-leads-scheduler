<?php
declare(strict_types=1);

namespace App\Providers;

use App\Repositories\LeadRepository;
use App\Repositories\PartnerRepository;
use App\Services\Lead\LeadBatchService;
use App\Services\Schedule\ScheduleService;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(
            ScheduleService::class,
            fn() => new ScheduleService(new PartnerRepository(), new LeadRepository())
        );
        $this->app->singleton(
            LeadRepository::class,
            fn() => new LeadRepository()
        );
        $this->app->singleton(
            PartnerRepository::class,
            fn() => new PartnerRepository()
        );;
        $this->app->singleton(
            LeadBatchService::class,
            fn() => new LeadBatchService(new LeadRepository())
        );

        if (!$this->app->environment('production')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Response::macro(
            'data',
            function ($data, $status = 200, $headers = [], $options = 0) {
                return response()
                    ->json(
                        ['data' => $data],
                        $status,
                        $headers,
                        $options
                    );
            }
        );
    }
}
