<?php
namespace Larashield\Providers;

use Illuminate\Support\ServiceProvider;

class LarashieldServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register bindings if needed
    }

    public function boot(): void
    {
        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        // Load config
        $this->publishes([__DIR__.'/../Config/larashield.php'=>config_path('larashield.php')], 'config');
        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // Register console commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Larashield\Console\InstallCommand::class,
            ]);
        }
    }

}
