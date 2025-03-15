<?php

use Ihasan\Bkash\Http\Controllers\BkashController;
use Illuminate\Support\Facades\Route;

Route::get('/callback', [BkashController::class, 'callback'])->name('callback');
Route::get('/success', [BkashController::class, 'success'])->name('success');
Route::get('/failed', [BkashController::class, 'failed'])->name('failed');
