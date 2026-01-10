<?php

namespace Larashield\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class InstallCommand extends Command
{
    protected $signature = 'larashield:install';
    protected $description = 'Install and setup Larashield (publish configs, models, policies, and run migrations)';

    public function handle(): int
    {
        $this->info('🚀 Starting Larashield installation...');

        // 1️⃣ Publish Sanctum migration if not exists
        $this->publishSanctum();

        // 2️⃣ Publish Larashield resources
        $this->publishAndMergeConfig();
        $this->stepPublish('Larashield routes', 'larashield-routes');
        $this->stepPublish('Larashield controllers', 'larashield-controllers');
        $this->stepPublish('Larashield models', 'larashield-models');
        $this->stepPublish('Larashield policies', 'larashield-policies');
        $this->stepPublish('Larashield traits', 'larashield-traits');
        // 3️⃣ Install Auditing package
        $this->installAuditingPackage();

        // 4️⃣ Publish Auditing config
        $this->publishAuditConfig();

        // 5️⃣ Patch models for auditing
        $models = [
            app_path('Models/Larashield/User.php'),
            app_path('Models/Larashield/PermissionGroup.php'),
        ];

        // 6️⃣ Publish Auditing migration
        $this->createAuditingMigration();

        // 7️⃣ Run all migrations
        $this->info('⚙️ Running migrations...');
        Artisan::call('migrate', ['--force' => true]);
        $this->info(Artisan::output());

        $this->seedSetupConfig();

        $this->info('✅ Larashield installation complete.');
        return self::SUCCESS;
    }

    protected function publishSanctum(): void
    {
        $migrationExists = glob(database_path('migrations/*_create_personal_access_tokens_table.php'));
        if (empty($migrationExists)) {
            $this->info('Publishing Sanctum config & migrations...');
            Artisan::call('vendor:publish', [
                '--provider' => "Laravel\\Sanctum\\SanctumServiceProvider",
                '--force' => true,
            ]);
            $this->info(Artisan::output());
        } else {
            $this->warn('⚠ Sanctum migration already exists, skipped.');
        }
    }

    protected function stepPublish(string $label, string $tag, bool $force = true): void
    {
        $this->info("📦 Publishing {$label}...");
        Artisan::call('vendor:publish', [
            '--provider' => "Larashield\\Providers\\LarashieldServiceProvider",
            '--tag' => $tag,
            '--force' => $force,
        ]);
        $this->info(Artisan::output());
    }

    protected function publishAndMergeConfig(): void
    {
        $this->info('📦 Processing Larashield configs...');

        $configPath = config_path('setup-config.php');
        $packageConfigPath = __DIR__ . '/../Config/setup-config.php';

        // Publish other configs normally
        $this->stepPublish('Larashield basic configs', 'larashield-config', false);

        if (file_exists($configPath)) {
            $this->info('🔄 Merging setup-config.php...');
            $existingConfig = require $configPath;
            $packageConfig = require $packageConfigPath;

            // Perform a smart merge
            $mergedConfig = $this->smartMerge($packageConfig, $existingConfig);
            
            $content = "<?php\n\nreturn " . $this->prettyPrintArray($mergedConfig) . ";\n";
            file_put_contents($configPath, $content);
            $this->info('✅ Merged package defaults with your existing setup-config.php');
        } else {
            $this->stepPublish('Larashield setup config', 'larashield-config', true);
        }
    }

    /**
     * Smartly merge package config with existing config
     */
    protected function smartMerge(array $package, array $existing): array
    {
        $merged = array_replace_recursive($package, $existing);

        // Special handling for permission_groups to merge by name instead of index
        if (isset($package['permission_groups']) && isset($existing['permission_groups'])) {
            $groups = [];
            foreach ($package['permission_groups'] as $group) {
                $groups[$group['name']] = $group;
            }
            foreach ($existing['permission_groups'] as $group) {
                if (isset($groups[$group['name']])) {
                    $groups[$group['name']]['permissions'] = array_unique(array_merge(
                        $groups[$group['name']]['permissions'],
                        $group['permissions']
                    ));
                } else {
                    $groups[$group['name']] = $group;
                }
            }
            $merged['permission_groups'] = array_values($groups);
        }

        // Special handling for simple list arrays to ensure uniqueness and appending
        $listKeys = ['roles', 'permissions_list', 'protected_roles', 'protected_permissions'];
        foreach ($listKeys as $key) {
            if (isset($package[$key]) && isset($existing[$key])) {
                $merged[$key] = array_unique(array_merge($package[$key], $existing[$key]));
                $merged[$key] = array_values($merged[$key]); // Reset indices
            }
        }

        // Special handling for role_permissions nested lists
        if (isset($package['role_permissions']) && isset($existing['role_permissions'])) {
            foreach ($package['role_permissions'] as $role => $permissions) {
                if (isset($existing['role_permissions'][$role])) {
                    $merged['role_permissions'][$role] = array_unique(array_merge(
                        $permissions,
                        $existing['role_permissions'][$role]
                    ));
                    $merged['role_permissions'][$role] = array_values($merged['role_permissions'][$role]);
                }
            }
        }

        return $merged;
    }

    /**
     * Pretty print array for config file
     */
    protected function prettyPrintArray(array $array, int $level = 1): string
    {
        $indent = str_repeat('    ', $level);
        $prevIndent = str_repeat('    ', $level - 1);
        
        $isAssoc = array_keys($array) !== range(0, count($array) - 1);
        
        $output = "[\n";
        foreach ($array as $key => $value) {
            $output .= $indent;
            if ($isAssoc) {
                $output .= var_export($key, true) . ' => ';
            }
            
            if (is_array($value)) {
                $output .= $this->prettyPrintArray($value, $level + 1);
            } else {
                $output .= var_export($value, true);
            }
            $output .= ",\n";
        }
        $output .= $prevIndent . "]";
        
        return $output;
    }

    protected function installAuditingPackage(): void
    {
        $composerPath = base_path('composer.json');
        $composerJson = json_decode(file_get_contents($composerPath), true);

        if (!isset($composerJson['require']['owen-it/laravel-auditing'])) {
            $this->info('📦 Adding "owen-it/laravel-auditing:^14.0" to composer.json...');
            $composerJson['require']['owen-it/laravel-auditing'] = "^14.0";
            file_put_contents($composerPath, json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            $this->info('🔄 Running composer update to install auditing package...');
            passthru('composer update owen-it/laravel-auditing');
        } else {
            $this->info('✅ "owen-it/laravel-auditing" already present in composer.json');
        }
    }

    protected function publishAuditConfig(): void
    {
        $this->info('📦 Publishing Auditing config...');
        Artisan::call('vendor:publish', [
            '--provider' => "OwenIt\Auditing\AuditingServiceProvider",
            '--tag' => 'config',
            '--force' => true,
        ]);
        $this->info(Artisan::output());
    }

    protected function createAuditingMigration(): void
    {
        $auditMigrationExists = glob(database_path('migrations/*_create_audits_table.php'));
        if (!empty($auditMigrationExists)) {
            $this->info('✅ Auditing migration already exists, skipped.');
            return;
        }

        $this->info('📦 Creating auditing migration...');
        Artisan::call('vendor:publish', [
            '--provider' => "OwenIt\Auditing\AuditingServiceProvider",
            '--tag' => 'migrations',
            '--force' => true,
        ]);
        $this->info(Artisan::output());

        $publishedMigrations = glob(database_path('migrations/*_create_audits_table.php'));
        if (!empty($publishedMigrations)) {
            $migrationFile = $publishedMigrations[0];
            $timestamp = date('Y_m_d_His');
            $newName = database_path("migrations/{$timestamp}_create_audits_table.php");
            rename($migrationFile, $newName);
            $this->info("✅ Auditing migration created: " . basename($newName));
        }
    }

    protected function seedSetupConfig(): void
    {
        $this->info('🌱 Seeding roles, users, and permissions...');
        Artisan::call('db:seed', [
            '--class' => 'Larashield\\database\\seeders\\SetupConfigSeeder',
            '--force' => true,
        ]);
        $this->info(Artisan::output());
    }
}
