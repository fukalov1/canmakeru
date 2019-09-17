<?php

use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {


    $router->get('/', 'HomeController@index')->name('admin.home');
    $router->get('/export_photos', 'HomeController@exportImage2YandexDisk');

    $router->post('/refresh_token', 'HomeController@refreshToken');
    $router->resource('customers', CustomerController::class);
    $router->resource('protokols', ProtokolController::class)->middleware('set_customer');
    $router->get('customer_chart', 'CustomerChartController@index')->middleware('set_customer');
    $router->get('customer_report', 'CustomerReportController@index');


});
