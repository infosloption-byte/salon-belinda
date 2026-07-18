<?php

use App\Http\Controllers\Admin\AppointmentController;
use App\Http\Controllers\Admin\AlbumController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GalleryController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])
        ->middleware('throttle:6,1')
        ->name('login.attempt');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Appointments
        Route::get('appointments', [AppointmentController::class, 'index'])->name('appointments.index');
        Route::patch('appointments/{appointment}/status', [AppointmentController::class, 'updateStatus'])->name('appointments.status');
        Route::delete('appointments/{appointment}', [AppointmentController::class, 'destroy'])->name('appointments.destroy');

        // Testimonials
        Route::get('testimonials', [TestimonialController::class, 'index'])->name('testimonials.index');
        Route::patch('testimonials/{testimonial}/status', [TestimonialController::class, 'updateStatus'])->name('testimonials.status');
        Route::delete('testimonials/{testimonial}', [TestimonialController::class, 'destroy'])->name('testimonials.destroy');

        // Gallery
        Route::get('gallery', [GalleryController::class, 'index'])->name('gallery.index');
        Route::post('gallery', [GalleryController::class, 'store'])->name('gallery.store');
        Route::delete('gallery/{galleryItem}', [GalleryController::class, 'destroy'])->name('gallery.destroy');

        // Wedding Albums
        Route::get('albums', [AlbumController::class, 'index'])->name('albums.index');
        Route::get('albums/create', [AlbumController::class, 'create'])->name('albums.create');
        Route::post('albums', [AlbumController::class, 'store'])->name('albums.store');
        Route::get('albums/{album}/edit', [AlbumController::class, 'edit'])->name('albums.edit');
        Route::put('albums/{album}', [AlbumController::class, 'update'])->name('albums.update');
        Route::delete('albums/{album}', [AlbumController::class, 'destroy'])->name('albums.destroy');
        Route::delete('albums/{album}/photos/{photo}', [AlbumController::class, 'destroyPhoto'])->name('albums.photos.destroy');

        // Services (menu)
        Route::get('services', [ServiceController::class, 'index'])->name('services.index');
        Route::post('services/categories', [ServiceController::class, 'storeCategory'])->name('services.categories.store');
        Route::delete('services/categories/{category}', [ServiceController::class, 'destroyCategory'])->name('services.categories.destroy');
        Route::post('services', [ServiceController::class, 'storeService'])->name('services.store');
        Route::put('services/{service}', [ServiceController::class, 'updateService'])->name('services.update');
        Route::delete('services/{service}', [ServiceController::class, 'destroyService'])->name('services.destroy');

        // Shop products
        Route::get('products', [ProductController::class, 'index'])->name('products.index');
        Route::get('products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('products', [ProductController::class, 'store'])->name('products.store');
        Route::get('products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

        // Orders
        Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
        Route::post('orders/{order}/mark-paid', [OrderController::class, 'markPaid'])->name('orders.markPaid');

        // Contact messages
        Route::get('contact-messages', [ContactMessageController::class, 'index'])->name('contact-messages.index');
        Route::post('contact-messages/{contactMessage}/read', [ContactMessageController::class, 'markRead'])->name('contact-messages.read');
        Route::post('contact-messages/{contactMessage}/replied', [ContactMessageController::class, 'markReplied'])->name('contact-messages.replied');
        Route::delete('contact-messages/{contactMessage}', [ContactMessageController::class, 'destroy'])->name('contact-messages.destroy');

        // My Account — every admin can manage their own name/email/password here
        Route::get('account', [UserController::class, 'account'])->name('account');
        Route::put('account', [UserController::class, 'updateAccount'])->name('account.update');

        // Admin Users — managing other admin accounts
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::get('users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });
});
