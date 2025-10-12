<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class WebServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            if (Auth::check()) {
                $view->with('user', [
                    'name' => Auth::user()->name,
                    'role' => Auth::user()->role ?? 'Member',
                ]);
            }
        });
    }
}
