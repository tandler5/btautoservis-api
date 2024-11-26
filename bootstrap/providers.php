<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\CustomValidationServiceProvider::class,
    App\Providers\TelescopeServiceProvider::class,
    Nuwave\Lighthouse\CacheControl\CacheControlServiceProvider::class,
    Nuwave\Lighthouse\WhereConditions\WhereConditionsServiceProvider::class,
];
