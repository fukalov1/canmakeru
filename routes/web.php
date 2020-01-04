<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/', 'MainController@index');
Route::get('/get_space', 'MainController@getSpaceDisk');
Route::post('/show_result', 'MainController@showResult');
Route::get('/preview/{year}/{month}/{file}', 'MainController@getPreview');
Route::get('/photo/{year}/{month}/{file}', 'MainController@getPhoto');
Route::get('/photo4pdf/{year}/{month}/{file}', 'MainController@getPhoto4Pdf');



Route::post('/uploads', 'MainController@saveResultMeter');

Route::get('/redirect', function () {
    $query = http_build_query([
        'client_id' => env('YANDEX_CLIENT_ID'),
        'redirect_uri' => 'http://127.0.0.1:8000/callback',
        'response_type' => 'code',
        'scope' => '',
    ]);

    return redirect('http://127.0.0.1:8000/oauth/authorize?'.$query);
});

Auth::routes();

Route::get('/login/customer', 'Auth\LoginController@showCustomerLoginForm');
Route::get('/logout', 'Auth\LoginController@logout');
//Route::get('/register/customer', 'Auth\RegisterController@showCustomerRegisterForm');

Route::post('/login/customer', 'Auth\LoginController@customerLogin');
//Route::post('/register/customer', 'Auth\RegisterController@createCustomer');

Route::view('/home', 'home')->middleware('auth');

Route::group(['middleware' => ['auth:customer']], function () {
    Route::get('/customer', 'CustomerController@index');
    Route::group(['prefix' => 'data'], function() {
        Route::post('/protokols', 'CustomerController@getDataProtokols');
        Route::get('/pdf/{id}', 'CustomerController@getPDF');
        Route::get('/profile', 'CustomerController@getProfile');
        Route::post('/statistic', 'CustomerController@getDataStatistic');
        Route::post('/report_days', 'CustomerController@getReportDays');
        Route::get('/workers', 'CustomerController@getListWorkers');
        Route::get('/auth_user', 'CustomerController@getCustomerId');
    });
});


Route::get('/home', 'HomeController@index')->name('home');
