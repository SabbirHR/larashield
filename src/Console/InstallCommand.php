<?php

namespace Larashield\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class InstallCommand extends Command
{
    protected $signature = 'larashield:install';
    protected $description = 'Install and setup Larashield (publish configs & run migrations)';

    public function handle(): int
    {
        $this->info('🚀 Starting Larashield installation...');

        // ✅ Publish Sanctum config + migration only if not already present
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

        $this->info('Publishing Larashield config...');
        Artisan::call('vendor:publish', [
            '--provider' => "Larashield\\Providers\\LarashieldServiceProvider",
            '--tag' => 'config',
            '--force' => true,
        ]);
        $this->info(Artisan::output());

        $this->info('Running migrations...');
        Artisan::call('migrate', ['--force' => true]);
        $this->info(Artisan::output());

        $this->info('✅ Larashield installation complete.');
        return self::SUCCESS;
    }
}
