<?php

namespace App\Providers;

use App\Services\Vat\VatService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Cache;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
        $this->app->register(TelescopeServiceProvider::class);
         // Registrujeme VatService jako singleton
        $this->app->singleton(VatService::class, function ($app) {
            return VatService::getInstance(); // Použijeme statickou metodu getInstance() pro Singleton
        });
        $this->app->singleton(OrderService::class, function ($app) {
            return OrderService::getInstance(); // Použijeme statickou metodu getInstance() pro Singleton
        });
        $this->app->singleton(BookingService::class, function ($app) {
            return BookingService::getInstance(); // Použijeme statickou metodu getInstance() pro Singleton
        });
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(function (\SocialiteProviders\Manager\SocialiteWasCalled $event) {
            $event->extendSocialite('google-one-tap', \SocialiteProviders\GoogleOneTap\Provider::class);
        });
    }
}
