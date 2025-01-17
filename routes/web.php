<?php

use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\UserController;
use App\Livewire\RealTimeMessage;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/', RealTimeMessage::class);

Route::get('/invoice', [InvoiceController::class, 'getInvoice']);

Route::get('/update-brands', function () {

    // Spuštění příkazu pro aktualizaci značek
    Artisan::call('update:brands');

    // Získáme výstup příkazu
    $output = Artisan::output();

    return $output;
});
Route::prefix('api')->group(function () {
    Route::prefix('users')->group(function () {
        Route::post('/notify', [UserController::class, 'notify']);
    });
});

Route::get('/update-models', function () {

    // Spuštění příkazu pro aktualizaci značek
    Artisan::call('update:models');

    // Získáme výstup příkazu
    $output = Artisan::output();

    return $output;
});
Route::get('/cache-clean', function () {

    // Spuštění příkazu pro aktualizaci značek
    Artisan::call('route:clear');
    Artisan::call('optimize:clear');
    Artisan::call('event:clear');
    Artisan::call('lighthouse:clear-cache');

    return "a";
});
