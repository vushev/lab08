<?php

namespace App\Providers;

use App\Alerting\AlertEvaluatorInterface;
use App\Alerting\TemperatureThresholdEvaluator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AlertEvaluatorInterface::class, TemperatureThresholdEvaluator::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
