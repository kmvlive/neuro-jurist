<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\View\Composers\SubscriptionComposer;
use Illuminate\Support\Facades\Mail;
use App\Mail\UniSenderTransport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Mail::extend('unisender', function (array $config = []) {
            return new UniSenderTransport();
        });
        
        View::composer('*', SubscriptionComposer::class);
    }
}
