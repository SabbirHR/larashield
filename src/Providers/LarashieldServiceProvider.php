<?php

namespace Larashield\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Larashield\Models\PermissionGroup;
use Larashield\Models\User;
use Larashield\Policies\PermissionGroupPolicy;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
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
        // set timezone automatically
        config(['app.timezone' => config('larashield.timezone', 'Asia/Dhaka')]);
        date_default_timezone_set(config('app.timezone'));
        // Publish routes into routes/api/api_larashield.php (keeps app routes/api.php intact)
        $this->publishes([
            __DIR__ . '/../routes/api.php' => base_path('routes/api/api_larashield.php'),
        ], 'larashield-routes');

        // // Publish controllers, models, policies if dev wants to override them
        // $this->publishes([
        //     __DIR__ . '/../Http/Controllers' => app_path('Http/Controllers/Larashield'),
        // ], 'larashield-controllers');

        // $this->publishes([
        //     __DIR__ . '/../Models' => app_path('Models/Larashield'),
        // ], 'larashield-models');

        // $this->publishes([
        //     __DIR__ . '/../Policies' => app_path('Policies/Larashield'),
        // ], 'larashield-policies');

        // Publish migrations (so app can edit/migrate)
        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'larashield-migrations');

        // Publish Traits so users can override or customize them
        $this->publishes([
            __DIR__ . '/../Traits/CustomAuditable.php' => app_path('Traits/CustomAuditable.php'),
        ], 'larashield-traits');
        // Load config
        $this->publishes([__DIR__ . '/../Config/larashield.php' => config_path('larashield.php')], 'config');
        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
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
        
        // Auto-register all policies
        $this->registerPolicies();
        
        // Bind the route parameter to the model with eager loading
        Route::bind('permission_group', function ($value) {
            $permissionGroup = PermissionGroup::with([
                'permissions:id,name',
                'permission_group_has_permission.permission'
            ])->findOrFail($value);

            Log::info('[Route Binding] Loaded permission_group', [
                'id' => $permissionGroup->id,
                'class' => get_class($permissionGroup)
            ]);

            return $permissionGroup;
        });


        // Other model bindings
        Route::model('user', User::class);
        Route::model('role', Role::class);

        // Register Spatie permission middleware aliases
        $router = $this->app['router'];
        $router->aliasMiddleware('role', RoleMiddleware::class);
        $router->aliasMiddleware('permission', PermissionMiddleware::class);
        $router->aliasMiddleware('role_or_permission', RoleOrPermissionMiddleware::class);
    }

    /**
     * Auto-discover and register all policies from the Policies directory
     */
    protected function registerPolicies(): void
    {
        $policies = $this->discoverPolicies();
        
        foreach ($policies as $model => $policy) {
            Gate::policy($model, $policy);
            Log::info('[LarashieldServiceProvider] Policy registered', [
                'model' => $model,
                'policy' => $policy,
            ]);
        }
    }

    /**
     * Discover all policies in the Policies directory
     * 
     * @return array<string, string> Array of model => policy mappings
     */
    protected function discoverPolicies(): array
    {
        $policies = [];
        $policiesPath = __DIR__ . '/../Policies';
        
        if (!is_dir($policiesPath)) {
            return $policies;
        }
        
        $policyFiles = glob($policiesPath . '/*Policy.php');
        
        foreach ($policyFiles as $policyFile) {
            $policyClassName = basename($policyFile, '.php');
            $policyClass = "Larashield\\Policies\\{$policyClassName}";
            
            // Extract model name from policy name (e.g., UserPolicy -> User)
            $modelName = str_replace('Policy', '', $policyClassName);
            
            // Determine the full model class
            $modelClass = $this->resolveModelClass($modelName);
            
            if ($modelClass && class_exists($policyClass)) {
                $policies[$modelClass] = $policyClass;
            }
        }
        
        return $policies;
    }

    /**
     * Resolve the full model class name from the model name
     * 
     * @param string $modelName
     * @return string|null
     */
    protected function resolveModelClass(string $modelName): ?string
    {
        // Check if it's a Larashield model
        $larashieldModel = "Larashield\\Models\\{$modelName}";
        if (class_exists($larashieldModel)) {
            return $larashieldModel;
        }
        
        // Check if it's a Spatie Permission model (Role, Permission)
        $spatieModel = "Spatie\\Permission\\Models\\{$modelName}";
        if (class_exists($spatieModel)) {
            return $spatieModel;
        }
        
        // Check if it's a standard Laravel model
        $appModel = "App\\Models\\{$modelName}";
        if (class_exists($appModel)) {
            return $appModel;
        }
        
        return null;
    }
}
