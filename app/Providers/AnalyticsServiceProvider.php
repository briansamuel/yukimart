<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Invoice\InvoiceRepository;
use App\Repositories\Invoice\InvoiceRepositoryInterface;
use App\Repositories\Invoice\InvoiceItemRepository;
use App\Services\Tenant\Analytics\SalesAnalyticsService;
use App\Services\Tenant\Analytics\ProfitAnalyticsService;
use App\Services\Tenant\Analytics\BranchAnalyticsService;
use App\Services\Tenant\Analytics\RankingAnalyticsService;

class AnalyticsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register Repositories
        $this->app->bind(InvoiceRepositoryInterface::class, InvoiceRepository::class);
        $this->app->singleton(InvoiceRepository::class, function ($app) {
            return new InvoiceRepository();
        });
        $this->app->singleton(InvoiceItemRepository::class, function ($app) {
            return new InvoiceItemRepository();
        });

        // Register Services
        $this->app->singleton(SalesAnalyticsService::class, function ($app) {
            return new SalesAnalyticsService(
                $app->make(InvoiceRepository::class),
                $app->make(InvoiceItemRepository::class)
            );
        });

        $this->app->singleton(ProfitAnalyticsService::class, function ($app) {
            return new ProfitAnalyticsService();
        });

        $this->app->singleton(BranchAnalyticsService::class, function ($app) {
            return new BranchAnalyticsService(
                $app->make(InvoiceRepository::class),
                $app->make(ProfitAnalyticsService::class)
            );
        });

        $this->app->singleton(RankingAnalyticsService::class, function ($app) {
            return new RankingAnalyticsService(
                $app->make(InvoiceRepository::class),
                $app->make(InvoiceItemRepository::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}

