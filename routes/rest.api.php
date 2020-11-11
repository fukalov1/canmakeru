<?php
/*
|--------------------------------------------------------------------------
| API Маршрутизация
|--------------------------------------------------------------------------
|
| Здесь живут API роуты для сайта. Префиксы описаны в Providers/RouteServiceProviders
|
*/

use Illuminate\Support\Facades\Route;

/**
 * REST API
 * Rest Api версии v1
 *
 * @url partner/v1/
 */
Route::group([
    'namespace' => 'v1',
    'middleware' => 'api',
    'prefix' => 'v1'
], function() {

    // регистрация запроса для создания чека
    Route::get('/check-create', 'GeneralClientRestApi@checkCreate');

});
