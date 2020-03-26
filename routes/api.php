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

Route::post('user', 'UserController@store');

Route::middleware('auth:api')->group(function ($route) {
    $route->get('user', 'UserController@show');
    $route->resource('game/{game}/message', 'GameMessageController')->only('index', 'store');;
    $route->resource('game/{game}/player/card', 'GamePlayerCardController')->only('index');
    $route->resource('game/{game}/player', 'GamePlayerController')->only('store', 'show', 'update', 'destroy');
    $route->resource('game', 'GameController')->only('store', 'show', 'update');;
});
