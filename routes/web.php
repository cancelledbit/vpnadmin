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


Auth::routes();

Route::get('/', 'VpnController@index')->middleware('auth')->name('home');
Route::get('/edit/{id}','VpnController@edit')->middleware('auth')->name('edit');
Route::post('/edit','VpnController@edit')->middleware('auth')->name('edit');
Route::get('/edit','VpnController@edit')->middleware('auth')->name('edit');
Route::get('/save','VpnController@save')->middleware('auth')->name('save');
Route::get('/remove/{id}','VpnController@remove')->middleware('auth')->name('save');
