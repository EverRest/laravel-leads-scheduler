<?php
declare(strict_types=1);

namespace App\Providers;

use App\Repositories\LeadRepository;
use App\Repositories\LeadResultRepository;
use App\Repositories\PartnerRepository;
use App\Services\LeadResultService;
use App\Services\ScheduleService;
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
            LeadResultService::class,
            fn() => new LeadResultService(new LeadResultRepository(), new LeadRepository())
        );
        $this->app->singleton(
            LeadRepository::class,
            fn() => new LeadRepository()
        );
        $this->app->singleton(
            LeadResultRepository::class,
            fn() => new LeadResultRepository()
        );
        $this->app->singleton(
            PartnerRepository::class,
            fn() => new PartnerRepository()
        );
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
