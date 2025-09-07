<?php
namespace Larashield\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Artisan;

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
        // Auto-setup when installing the package
        if ($this->app->runningInConsole()) {
            $this->autoSetup();
        }
    }

    protected function autoSetup(): void
    {
        // Publish Sanctum config
        Artisan::call('vendor:publish', [
            '--provider' => "Laravel\\Sanctum\\SanctumServiceProvider",
            '--force' => true,
        ]);

        // Publish Larashield config
        Artisan::call('vendor:publish', [
            '--provider' => "Larashield\\Providers\\LarashieldServiceProvider",
            '--tag' => 'config',
            '--force' => true,
        ]);

    }
}
