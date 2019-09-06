<?php

use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index')->name('admin.home');
    $router->post('/refresh_token', 'HomeController@refreshToken');
    $router->resource('customers', CustomerController::class);
    $router->resource('protokols', ProtokolController::class);


});
