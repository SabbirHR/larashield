<?php

namespace Larashield\Providers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Larashield\Models\PermissionGroup;
use Larashield\Models\User;
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
        ], 'larashield-config');
        // Publish routes into routes/api/api_larashield.php (keeps app routes/api.php intact)
        $this->publishes([
            __DIR__ . '/../routes/api.php' => base_path('routes/api/api_larashield.php'),
        ], 'larashield-routes');

        // Publish controllers, models, policies if dev wants to override them
        $this->publishes([
            __DIR__ . '/../Http/Controllers' => app_path('Http/Controllers/Larashield'),
        ], 'larashield-controllers');

        $this->publishes([
            __DIR__ . '/../Models' => app_path('Models/Larashield'),
        ], 'larashield-models');

        $this->publishes([
            __DIR__ . '/../Policies' => app_path('Policies/Larashield'),
        ], 'larashield-policies');

        // Publish migrations (so app can edit/migrate)
        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'larashield-migrations');
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
        // 3️⃣ Bind the route parameter to the model with eager loading
        Route::bind('permission_group', function ($value) {
            return PermissionGroup::with([
                'permission',
                'permission_group_has_permission.permission'
            ])->findOrFail($value);
        });

        // 4️⃣ Other model bindings
        Route::model('user', User::class);
        Route::model('role', Role::class);
      

        Log::info('LarashieldServiceProvider booted');
    }
}
