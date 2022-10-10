<?php


namespace Softnio\UtilityServices;


use Illuminate\Support\ServiceProvider;
use Softnio\UtilityServices\Processor\ChecklistProcessor;

class UtilityServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(UtilityService::class, function () {
            return new UtilityService();
        });

        $this->app->register(ChecklistProcessor::class);
    }

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/routes.php');
        $this->loadViewsFrom(__DIR__.'/Views', 'Utility');
    }
}