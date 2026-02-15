<?php

namespace App\Providers;

use App\Services\ClaimAccountService;
use App\Services\GoogleTokenVerifier;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(GoogleTokenVerifier::class, function () {
            $clientId = config('services.google.client_id');
            if (empty($clientId)) {
                throw new \RuntimeException('GOOGLE_CLIENT_ID must be set for Google token verification.');
            }
            return new GoogleTokenVerifier($clientId);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
