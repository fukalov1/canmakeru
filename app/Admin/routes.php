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
    $router->resource('transactions', TrancactionControler::class)->middleware('set_customer');

    $router->get('customer_chart', 'CustomerChartController@index')->middleware('set_customer');
    $router->get('customer_report', 'CustomerReportController@index');
    $router->get('export-fgis', 'CustomerController@exportXmlToFGIS');
    $router->post('export-package-fgis', 'CustomerController@exportPackageXmlToFGIS');
    $router->get('export-one-fgis/{id}', 'CustomerController@exportOneXmlToFGIS');

    $router->post('convert-xls-xml', 'CustomerController@convertXlsToXml');

    $router->resource('slave_customers', SlaveCustomerController::class)->middleware('set_customer');




});
