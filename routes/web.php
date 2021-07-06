<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;

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

Route::prefix('export')->group(function() {
    Route::get('physician/{physician}', [PaymentController::class, 'export_physician']);
    Route::get('hospital/{hospital}', [PaymentController::class, 'export_hospital']);
});

Route::get('/', [PaymentController::class, 'index']);
