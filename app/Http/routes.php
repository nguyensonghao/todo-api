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

Route::get('/', function () {
    return view('welcome');
});

Route::post('/register', 'AuthController@register');
Route::post('/login', 'AuthController@login');
Route::group([
    'prefix' => 'todo',
    'middleware' => ['userlogin']
], function () {
    Route::get('/list', 'TodoController@all');
    Route::post('/add', 'TodoController@add');
    Route::put('/update', 'TodoController@update');
    Route::delete('/{id}', 'TodoController@remove');
    Route::put('/update-status', 'TodoController@update_status');
});
