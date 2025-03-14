<?php

namespace Ihasan\Bkash\Commands;

use Ihasan\Bkash\Facades\Bkash;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class BkashSetupCommand extends Command
{
    protected $signature = 'bkash:setup {--test : Test the connection to bKash API}';

    protected $description = 'Setup and verify bKash integration';

    public function handle()
    {
        $this->info('Setting up bKash integration...');
        $configPublished = false;
        $migrationsPublished = false;

        if (! file_exists(config_path('bkash.php'))) {
            $this->error('bKash config file not found. Publishing config...');
            $this->call('vendor:publish', [
                '--tag' => 'bkash-config',
            ]);
            $this->info('Config published.');
            $configPublished = true;
        }

        $migrationsExist = false;
        $migrationFiles = File::glob(database_path('migrations/*_create_bkash_payments_table.php'));

        if (count($migrationFiles) > 0) {
            $migrationsExist = true;
        }

        if (! $migrationsExist) {
            $this->error('bKash migrations not found. Publishing migrations...');
            $this->call('vendor:publish', [
                '--tag' => 'bkash-migrations',
            ]);
            $this->info('Migrations published.');
            $migrationsPublished = true;
        }

        if (! Schema::hasTable('bkash_payments')) {
            $this->error('bKash tables not found. Running migrations...');
            $this->call('migrate');
        }

        if ($configPublished) {
            $this->call('config:clear');
        }

        $credentials = config('bkash.credentials');
        if (empty($credentials['app_key']) || empty($credentials['app_secret']) ||
            empty($credentials['username']) || empty($credentials['password'])) {
            $this->error('bKash credentials are not properly configured in your .env file.');
            $this->info('Please add the following to your .env file:');
            $this->line('BKASH_SANDBOX=true');
            $this->line('BKASH_APP_KEY=your-app-key');
            $this->line('BKASH_APP_SECRET=your-app-secret');
            $this->line('BKASH_USERNAME=your-username');
            $this->line('BKASH_PASSWORD=your-password');

            return 1;
        }

        if ($this->option('test')) {
            $this->info('Testing connection to bKash API...');

            try {
                $token = Bkash::getToken();
                $this->info('✓ Successfully connected to bKash API');
                $this->line('Token: '.substr($token, 0, 20).'...');

                $this->info('bKash integration is properly set up and working!');

                return 0;
            } catch (\Exception $e) {
                $this->error('Failed to connect to bKash API: '.$e->getMessage());

                return 1;
            }
        }

        $this->info('bKash integration is properly set up!');
        $this->info('Run `php artisan bkash:setup --test` to test the connection to bKash API.');

        return 0;
    }
}
