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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/', 'MainController@index');
Route::get('/get_space', 'MainController@getSpaceDisk');
Route::get('/preview/{year}/{month}/{file}', 'MainController@getPreview');

Route::post('/uploads', 'MainController@uploadFile');

Route::get('/redirect', function () {
    $query = http_build_query([
        'client_id' => env('YANDEX_CLIENT_ID'),
        'redirect_uri' => 'http://127.0.0.1:8000/callback',
        'response_type' => 'code',
        'scope' => '',
    ]);

    return redirect('http://127.0.0.1:8000/oauth/authorize?'.$query);
});
