<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Payment;

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
Route::prefix('payment')->group(function () {
    Route::prefix('recipient')->group(function () {
        Route::get('physician/{physician}', [PaymentController::class, 'api_show_physician_payments']);
        Route::get('hospital/{hospital}', [PaymentController::class, 'api_show_hospital_payments']);
        Route::get('search', [PaymentController::class, 'api_search_recipient']);
    });
    
    Route::get('{payment}', [PaymentController::class, 'api_show']);
});
