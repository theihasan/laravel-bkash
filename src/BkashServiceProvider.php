<?php

namespace Ihasan\Bkash;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class BkashServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-bkash')
            ->hasConfigFile()
            ->hasMigrations([
                'create_bkash_payments_table',
                'create_bkash_refunds_table'
            ])
            ->hasRoute('web');
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(Bkash::class, function ($app) {
            return new Bkash;
        });
    }
}
