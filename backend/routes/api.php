<?php

use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\ContactMessageController;
use App\Http\Controllers\Api\GalleryController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\TestimonialController;
use Illuminate\Support\Facades\Route;

// --- Main site (frontend) ---
Route::get('/services', [ServiceController::class, 'index']);
Route::get('/gallery', [GalleryController::class, 'index']);
Route::get('/testimonials', [TestimonialController::class, 'index']);
Route::post('/testimonials', [TestimonialController::class, 'store'])->middleware('throttle:5,1');
Route::post('/appointments', [AppointmentController::class, 'store'])->middleware('throttle:5,1');
Route::post('/contact', [ContactMessageController::class, 'store'])->middleware('throttle:5,1');

// --- Shop ---
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{slug}', [ProductController::class, 'show']);
Route::post('/orders', [OrderController::class, 'store'])->middleware('throttle:10,1');
Route::get('/orders/{orderNumber}', [OrderController::class, 'show']);
