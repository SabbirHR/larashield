<?php

namespace Larashield\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Larashield\Models\User;
use Larashield\Models\PermissionGroup;
use Spatie\Permission\Models\Role;

class LarashieldServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Merge package configs with application's configs
        $this->mergeConfigFrom(__DIR__ . '/../Config/larashield.php', 'larashield');
        $this->mergeConfigFrom(__DIR__ . '/../Config/permission.php', 'permission');
        $this->mergeConfigFrom(__DIR__ . '/../Config/setup-config.php', 'setup-config');
    }

    public function boot(): void
    {
        // Publish config files
        $this->publishes([
            __DIR__ . '/../Config/larashield.php' => config_path('larashield.php'),
            __DIR__ . '/../Config/permission.php' => config_path('permission.php'),
            __DIR__ . '/../Config/setup-config.php' => config_path('setup-config.php'),
        ], 'config');
        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        // Load config
        $this->publishes([__DIR__ . '/../Config/larashield.php' => config_path('larashield.php')], 'config');
        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        // Register console commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Larashield\Console\InstallCommand::class,
            ]);
        }
        // Ensure Spatie's permission config is registered automatically
        if (! config()->has('permission')) {
            $this->publishes([
                __DIR__ . '/../Config/permission.php' => config_path('permission.php'),
            ], 'permission-config');

            config([
                'permission' => require __DIR__ . '/../Config/permission.php',
            ]);
        }

        // ✅ Route model bindings
        Route::model('user', User::class);
        // Bind route parameter to model
        Route::model('permission_group', PermissionGroup::class);

        // Optional: fallback if not found
        Route::bind('permission_group', function ($value) {
            return PermissionGroup::findOrFail($value);
        });
        Route::model('role', Role::class);
    }
}
