<?php

use App\Http\Controllers\User\ApiController;
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

Route::post('/v1','ApiController@apiV1')->name('userApiKey');

Route::post('e-fund',  [ApiController::class,'e_fund']);

Route::any('verify',  [ApiController::class,'verify_username']);
