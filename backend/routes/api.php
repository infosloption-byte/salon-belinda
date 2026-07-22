<?php

use App\Http\Controllers\Api\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Api\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Api\Admin\ProductCategoryController as AdminProductCategoryController;
use App\Http\Controllers\Api\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Api\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\AlbumController;
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
Route::get('/albums', [AlbumController::class, 'index']);
Route::get('/albums/categories', [AlbumController::class, 'categories']);
Route::get('/albums/{slug}', [AlbumController::class, 'show']);
Route::get('/testimonials', [TestimonialController::class, 'index']);
Route::post('/testimonials', [TestimonialController::class, 'store'])->middleware('throttle:5,1');
Route::post('/appointments', [AppointmentController::class, 'store'])->middleware('throttle:5,1');
Route::post('/contact', [ContactMessageController::class, 'store'])->middleware('throttle:5,1');

// --- Shop ---
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{slug}', [ProductController::class, 'show']);
Route::post('/orders', [OrderController::class, 'store'])->middleware('throttle:10,1');
Route::get('/orders/{orderNumber}', [OrderController::class, 'show']);

// --- Admin (new React admin app — see /admin) ---
// Mirrors routes/web.php's admin.* group 1:1, JSON instead of Blade. Kept
// side-by-side with the Blade admin until every module below is ported and
// verified working against the React app — see SAAS-ROADMAP.md Phase 2.
Route::prefix('admin')->name('api.admin.')->group(function () {
    Route::post('login', [AdminAuthController::class, 'login'])
        ->middleware('throttle:6,1')
        ->name('login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout');
        Route::get('me', [AdminAuthController::class, 'me'])->name('me');

        // Shared area: both admin and staff logins can reach these.
        Route::middleware('staff_or_admin')->group(function () {
            Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        });

        // Admin-only area.
        Route::middleware('admin')->group(function () {
            Route::get('services', [AdminServiceController::class, 'index'])->name('services.index');
            Route::post('services/categories', [AdminServiceController::class, 'storeCategory'])->name('services.categories.store');
            Route::delete('services/categories/{category}', [AdminServiceController::class, 'destroyCategory'])->name('services.categories.destroy');
            Route::post('services', [AdminServiceController::class, 'storeService'])->name('services.store');
            Route::put('services/{service}', [AdminServiceController::class, 'updateService'])->name('services.update');
            Route::delete('services/{service}', [AdminServiceController::class, 'destroyService'])->name('services.destroy');

            // Products (multipart create/update — send FormData with a
            // "_method": "PUT" field on update requests, Laravel's method
            // spoofing middleware reads that from multipart POST bodies).
            Route::get('products', [AdminProductController::class, 'index'])->name('products.index');
            Route::post('products', [AdminProductController::class, 'store'])->name('products.store');
            Route::put('products/{product}', [AdminProductController::class, 'update'])->name('products.update');
            Route::delete('products/{product}', [AdminProductController::class, 'destroy'])->name('products.destroy');
            Route::post('products/categories', [AdminProductCategoryController::class, 'store'])->name('products.categories.store');
            Route::put('products/categories/{productCategory}', [AdminProductCategoryController::class, 'update'])->name('products.categories.update');
            Route::delete('products/categories/{productCategory}', [AdminProductCategoryController::class, 'destroy'])->name('products.categories.destroy');
        });
    });
});
