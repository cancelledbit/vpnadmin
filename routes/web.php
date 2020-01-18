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
Route::get('/remove/{id}','VpnController@remove')->middleware('auth')->name('remove');


//admin
Route::get('/admin','AdminController@index')->middleware('auth')->name('admin');
Route::get('/admin/users','AdminController@userList')->middleware('auth')->name('admin.userlist');
Route::get('/admin/users/edit/{id}','AdminController@getUserEdit')->middleware('auth')->name('admin.getuseredit');
Route::post('/admin/users/edit/{id}','AdminController@postUserEdit')->middleware('auth')->name('admin.podtuseredit');