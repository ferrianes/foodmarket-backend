<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FoodController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Api\MidtransController;
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

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('admin')
    ->middleware(['auth:sanctum', 'admin'])
    ->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('transactions/{id}/status/{status}', [TransactionController::class, 'changeStatus'])->name('transactions.changeStatus');
        Route::resources([
            'users' => UserController::class,
            'food' => FoodController::class,
            'transactions' => TransactionController::class
        ]);
    });

Route::get('midtrans/success', [MidtransController::class, 'success']);
Route::get('midtrans/unfinish', [MidtransController::class, 'unfinish']);
Route::get('midtrans/error', [MidtransController::class, 'error']);
