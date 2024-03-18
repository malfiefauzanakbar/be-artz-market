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
Route::middleware(['cors'])->group(function () {
    Route::post('/register', 'AuthController@register');
    Route::post('/login', 'AuthController@login');
    Route::post('/logout', 'AuthController@logout');
    Route::post('/update/{id?}', 'AuthController@update');
    Route::get('/detail/{id?}', 'AuthController@show');

    Route::get('/categoryproduct', 'CategoryProductController@index');
    Route::post('/categoryproduct', 'CategoryProductController@store');
    Route::get('/categoryproduct/{id?}', 'CategoryProductController@show');
    Route::post('/categoryproduct/update/{id?}', 'CategoryProductController@update');
    Route::delete('/categoryproduct/{id?}', 'CategoryProductController@destroy');

    Route::get('/product', 'ProductController@index');
    Route::post('/product', 'ProductController@store');
    Route::get('/product/{id?}', 'ProductController@show');
    Route::post('/product/update/{id?}', 'ProductController@update');
    Route::delete('/product/{id?}', 'ProductController@destroy');

    Route::get('/tax', 'TaxController@index');
    Route::post('/tax', 'TaxController@store');
    Route::get('/tax/{id?}', 'TaxController@show');
    Route::post('/tax/update/{id?}', 'TaxController@update');
    Route::delete('/tax/{id?}', 'TaxController@destroy');

    Route::get('/cart/{userId?}', 'CartController@index');
    Route::post('/cart', 'CartController@store');
    Route::post('/cart/update/{id?}', 'CartController@update');
    Route::delete('/cart/{id?}', 'CartController@destroy');
    Route::get('/cart/count/{userId?}', 'CartController@countCart');

    Route::get('/transaction/{userId?}', 'TransactionController@index');
    Route::post('/transaction', 'TransactionController@store');
    Route::get('/transaction/{userId?}/{id?}', 'TransactionController@show');
});

