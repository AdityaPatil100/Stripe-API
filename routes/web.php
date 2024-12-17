<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StripeController;

Route::get('/', [StripeController::class, 'checkout'])->name('checkout');
Route::post('/create-payment-intent', [StripeController::class, 'createPaymentIntent'])->name('createPaymentIntent');
