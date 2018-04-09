<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', 'ArticleController@index');
Route::get('/error', function (){
    return view('errors.503');
});
Route::get('show/{id}', 'ArticleController@show');
Route::get('image/{date?}', 'AdminController@image');

Route::match(['get', 'post'], 'login', 'AdminController@login');
Route::post('class/add', 'ArticleController@classAdd');
Route::post('remove', 'ArticleController@remove');
Route::post('like', 'ArticleController@like');
Route::post('upload', 'ArticleController@upload');
Route::match(['get', 'post'], 'add', 'ArticleController@add');
Route::match(['get', 'post'], 'edit/{id?}', 'ArticleController@edit');

