<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AppointmentController;
use App\Http\Controllers\Admin\AlbumController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GalleryCategoryController;
use App\Http\Controllers\Admin\GalleryController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductCategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SalonJobController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\StaffController;
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

    // Shared area: both admin and staff logins land here / use these.
    Route::middleware(['auth', 'staff_or_admin'])->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Customers
        Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::get('customers/create', [CustomerController::class, 'create'])->name('customers.create');
        Route::post('customers', [CustomerController::class, 'store'])->name('customers.store');
        Route::get('customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
        Route::get('customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
        Route::put('customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
        Route::delete('customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');

        // My Account — every logged-in user (admin or staff) manages their own login here
        Route::get('account', [UserController::class, 'account'])->name('account');
        Route::put('account', [UserController::class, 'updateAccount'])->name('account.update');

        // Jobs — the daily salon-operations screen (create/manage in one place)
        Route::get('jobs', [SalonJobController::class, 'index'])->name('jobs.index');
        Route::get('jobs/create', [SalonJobController::class, 'create'])->name('jobs.create');
        Route::post('jobs/quick-register-customer', [SalonJobController::class, 'quickRegisterCustomer'])->name('jobs.quickRegisterCustomer');
        Route::post('jobs', [SalonJobController::class, 'store'])->name('jobs.store');
        Route::get('jobs/{job}', [SalonJobController::class, 'show'])->name('jobs.show');
        Route::patch('jobs/{job}/status', [SalonJobController::class, 'updateStatus'])->name('jobs.status');
        Route::post('jobs/{job}/items', [SalonJobController::class, 'addItem'])->name('jobs.items.store');
        Route::delete('jobs/{job}/items/{item}', [SalonJobController::class, 'removeItem'])->name('jobs.items.destroy');
        Route::post('jobs/{job}/payments', [SalonJobController::class, 'addPayment'])->name('jobs.payments.store');
        Route::delete('jobs/{job}/payments/{payment}', [SalonJobController::class, 'removePayment'])->name('jobs.payments.destroy');
        Route::get('jobs/{job}/receipt', [SalonJobController::class, 'receiptPreview'])->name('jobs.receipt.preview');
        Route::get('jobs/{job}/receipt/download', [SalonJobController::class, 'receiptDownload'])->name('jobs.receipt.download');

        // Staff commission — shared route, but the controller scopes results
        // to "only their own" for staff logins and "all staff" for admins.
        Route::get('reports/staff-commission', [ReportController::class, 'staffCommission'])->name('reports.staffCommission');
    });

    Route::middleware(['auth', 'admin'])->group(function () {
        // Staff profiles
        Route::get('staff', [StaffController::class, 'index'])->name('staff.index');
        Route::get('staff/create', [StaffController::class, 'create'])->name('staff.create');
        Route::post('staff', [StaffController::class, 'store'])->name('staff.store');
        Route::get('staff/{staff}/edit', [StaffController::class, 'edit'])->name('staff.edit');
        Route::put('staff/{staff}', [StaffController::class, 'update'])->name('staff.update');
        Route::delete('staff/{staff}', [StaffController::class, 'destroy'])->name('staff.destroy');
        Route::post('staff/{staff}/toggle-active', [StaffController::class, 'toggleActive'])->name('staff.toggleActive');

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
        Route::post('gallery/categories', [GalleryCategoryController::class, 'store'])->name('gallery.categories.store');
        Route::put('gallery/categories/{galleryCategory}', [GalleryCategoryController::class, 'update'])->name('gallery.categories.update');
        Route::delete('gallery/categories/{galleryCategory}', [GalleryCategoryController::class, 'destroy'])->name('gallery.categories.destroy');

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
        Route::post('products/categories', [ProductCategoryController::class, 'store'])->name('products.categories.store');
        Route::put('products/categories/{productCategory}', [ProductCategoryController::class, 'update'])->name('products.categories.update');
        Route::delete('products/categories/{productCategory}', [ProductCategoryController::class, 'destroy'])->name('products.categories.destroy');

        // Orders
        Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
        Route::post('orders/{order}/mark-paid', [OrderController::class, 'markPaid'])->name('orders.markPaid');
        Route::get('orders/{order}/invoice', [OrderController::class, 'invoicePreview'])->name('orders.invoice.preview');
        Route::get('orders/{order}/invoice/download', [OrderController::class, 'invoiceDownload'])->name('orders.invoice.download');

        // Contact messages
        Route::get('contact-messages', [ContactMessageController::class, 'index'])->name('contact-messages.index');
        Route::post('contact-messages/{contactMessage}/read', [ContactMessageController::class, 'markRead'])->name('contact-messages.read');
        Route::post('contact-messages/{contactMessage}/replied', [ContactMessageController::class, 'markReplied'])->name('contact-messages.replied');
        Route::delete('contact-messages/{contactMessage}', [ContactMessageController::class, 'destroy'])->name('contact-messages.destroy');

        // Reports
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/revenue', [ReportController::class, 'revenue'])->name('reports.revenue');
        Route::get('reports/best-sellers', [ReportController::class, 'bestSellers'])->name('reports.bestSellers');
        Route::get('reports/low-stock', [ReportController::class, 'lowStock'])->name('reports.lowStock');
        Route::get('reports/appointments', [ReportController::class, 'appointments'])->name('reports.appointments');
        Route::get('reports/outstanding-balances', [ReportController::class, 'outstandingBalances'])->name('reports.outstandingBalances');

        // Activity Log
        Route::get('activity-log', [ActivityLogController::class, 'index'])->name('activity-log.index');

        // Admin Users — managing other admin/staff accounts
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::get('users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });
});
