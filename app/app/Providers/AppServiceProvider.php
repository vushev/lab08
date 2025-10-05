<?php

namespace App\Providers;

use App\Alerting\AlertEvaluatorInterface;
use App\Alerting\TemperatureThresholdEvaluator;
use App\Repositories\AlertRepositoryInterface;
use App\Repositories\MeasurementRepositoryInterface;
use App\Repositories\MeasurementRepository;
use App\Repositories\AlertRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AlertEvaluatorInterface::class, TemperatureThresholdEvaluator::class);
        $this->app->bind(MeasurementRepositoryInterface::class, MeasurementRepository::class);
        $this->app->bind(AlertRepositoryInterface::class, AlertRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
