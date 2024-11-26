<?php

namespace App\Providers;

use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CustomValidationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

      /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('valid_extras_for_service', function ($attribute, $value, $parameters, $validator) {
            // TODO: Jak lépe předat parenta
            $servicePath = str_replace(".extras.0.", ".service", $parameters[0]);

            $service = Arr::get($validator->getData(), $servicePath);

            // Zkontrolujte, zda kombinace exists
            return \DB::table('wp_latepoint_services_service_extras')
                ->where('service_id', $service)
                ->where('service_extra_id', $value)
                ->exists();

        }, 'Tato kombinace service_id a service_extra_id neexistuje.');

        Validator::extend('valid_extras_quantity', function ($attribute, $value, $parameters, $validator) {
            $extra = Arr::get($validator->getData(), $parameters[0]);
            return DB::table('wp_latepoint_service_extras')
            ->where('id', $extra)
            ->where('maximum_quantity', '>=', $value)
            ->exists();
          
        }, 'Požadované množství service extra není možné');

        Validator::extend('max_current_year', function ($attribute, $value) {
          
            $currentYear = (int) date('Y');
            return $value <= $currentYear;
          
        }, 'Rok musí být menší nebo roven letošnímu roku');
    }
}
