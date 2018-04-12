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

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
Route::get('/manage_account', 'HomeController@manageAccount')->name('manage_account');
Route::post('{user}/geta_access_token/', 'HomeController@getAccessToken')->name('get_access_token');
Route::get('get_access_token/oauth2callback', 'HomeController@oauth2callback')->name('oauth2callback');

Route::post('update', 'HomeController@update')->name('update');
// Route::post('channel/subscribe', 'HomeController@subscribe')->name('subscribe');
// Route::post('video/rate', 'HomeController@videoRate')->name('video_rate');
