<?php

namespace Ihasan\Bkash;

use Illuminate\Support\Facades\Http;
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
                'create_bkash_refunds_table',
            ]);

    }

    public function packageRegistered(): void
    {
        $this->app->singleton(Bkash::class, function ($app) {
            return new Bkash;
        });
    }

    public function packageBooted(): void
    {
        Http::macro('bkash', function (?string $token = null) {
            $baseUrl = config('bkash.sandbox')
                ? config('bkash.sandbox_base_url')
                : config('bkash.live_base_url');

            $version = config('bkash.version');

            $client = Http::baseUrl("{$baseUrl}/{$version}/tokenized/checkout")
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ]);

            if ($token) {
                $client = $client->withHeaders([
                    'Authorization' => $token,
                    'X-APP-Key' => config('bkash.credentials.app_key'),
                ]);
            }

            return $client;
        });

    }
}
