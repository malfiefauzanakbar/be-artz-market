<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register', array('middleware' => 'cors', 'uses' => 'AuthController@register'));
Route::post('/login', array('middleware' => 'cors', 'uses' => 'AuthController@login'));
Route::post('/logout', array('middleware' => 'cors', 'uses' => 'AuthController@logout'));

Route::get('/categoryproduct', array('middleware' => 'cors', 'uses' => 'CategoryProductController@index'));
Route::post('/categoryproduct', array('middleware' => 'cors', 'uses' => 'CategoryProductController@store'));
Route::get('/categoryproduct/{id?}', array('middleware' => 'cors', 'uses' => 'CategoryProductController@show'));
Route::post('/categoryproduct/update/{id?}', array('middleware' => 'cors', 'uses' => 'CategoryProductController@update'));
Route::delete('/categoryproduct/{id?}', array('middleware' => 'cors', 'uses' => 'CategoryProductController@destroy'));

Route::get('/product', array('middleware' => 'cors', 'uses' => 'ProductController@index'));
Route::post('/product', array('middleware' => 'cors', 'uses' => 'ProductController@store'));
Route::get('/product/{id?}', array('middleware' => 'cors', 'uses' => 'ProductController@show'));
Route::post('/product/update/{id?}', array('middleware' => 'cors', 'uses' => 'ProductController@update'));
Route::delete('/product/{id?}', array('middleware' => 'cors', 'uses' => 'ProductController@destroy'));

Route::get('/tax', array('middleware' => 'cors', 'uses' => 'TaxController@index'));
Route::post('/tax', array('middleware' => 'cors', 'uses' => 'TaxController@store'));
Route::get('/tax/{id?}', array('middleware' => 'cors', 'uses' => 'TaxController@show'));
Route::post('/tax/update/{id?}', array('middleware' => 'cors', 'uses' => 'TaxController@update'));
Route::delete('/tax/{id?}', array('middleware' => 'cors', 'uses' => 'TaxController@destroy'));

Route::get('/cart/{userId?}', array('middleware' => 'cors', 'uses' => 'CartController@index'));
Route::post('/cart', array('middleware' => 'cors', 'uses' => 'CartController@store'));
Route::post('/cart/update/{id?}', array('middleware' => 'cors', 'uses' => 'CartController@update'));
Route::delete('/cart/{id?}', array('middleware' => 'cors', 'uses' => 'CartController@destroy'));

Route::get('/transaction/{userId?}', array('middleware' => 'cors', 'uses' => 'TransactionController@index'));
Route::post('/transaction', array('middleware' => 'cors', 'uses' => 'TransactionController@store'));
Route::get('/transaction/{userId?}/{id?}', array('middleware' => 'cors', 'uses' => 'TransactionController@show'));

