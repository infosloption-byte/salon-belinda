<?php

use App\Http\Controllers\Api\Admin\ActivityLogController as AdminActivityLogController;
use App\Http\Controllers\Api\Admin\AlbumController as AdminAlbumController;
use App\Http\Controllers\Api\Admin\AppointmentController as AdminAppointmentController;
use App\Http\Controllers\Api\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Api\Admin\ContactMessageController as AdminContactMessageController;
use App\Http\Controllers\Api\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Api\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Api\Admin\GalleryCategoryController as AdminGalleryCategoryController;
use App\Http\Controllers\Api\Admin\GalleryController as AdminGalleryController;
use App\Http\Controllers\Api\Admin\JobController as AdminJobController;
use App\Http\Controllers\Api\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Api\Admin\ProductCategoryController as AdminProductCategoryController;
use App\Http\Controllers\Api\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Api\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Api\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Api\Admin\StaffController as AdminStaffController;
use App\Http\Controllers\Api\Admin\StaffShiftController as AdminStaffShiftController;
use App\Http\Controllers\Api\Admin\TestimonialController as AdminTestimonialController;
use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
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

            // Jobs (daily ops) — staff can reach these too; the controller
            // itself scopes/authorizes per-job access for non-admin users.
            Route::get('jobs', [AdminJobController::class, 'index'])->name('jobs.index');
            Route::get('jobs/create', [AdminJobController::class, 'create'])->name('jobs.create');
            Route::post('jobs/quick-register-customer', [AdminJobController::class, 'quickRegisterCustomer'])->name('jobs.quickRegisterCustomer');
            Route::post('jobs', [AdminJobController::class, 'store'])->name('jobs.store');
            Route::get('jobs/{job}', [AdminJobController::class, 'show'])->name('jobs.show');
            Route::patch('jobs/{job}/status', [AdminJobController::class, 'updateStatus'])->name('jobs.status');
            Route::post('jobs/{job}/items', [AdminJobController::class, 'addItem'])->name('jobs.items.store');
            Route::delete('jobs/{job}/items/{item}', [AdminJobController::class, 'removeItem'])->name('jobs.items.destroy');
            Route::post('jobs/{job}/payments', [AdminJobController::class, 'addPayment'])->name('jobs.payments.store');
            Route::delete('jobs/{job}/payments/{payment}', [AdminJobController::class, 'removePayment'])->name('jobs.payments.destroy');
            Route::get('jobs/{job}/receipt', [AdminJobController::class, 'receiptPreview'])->name('jobs.receipt.preview');
            Route::get('jobs/{job}/receipt/download', [AdminJobController::class, 'receiptDownload'])->name('jobs.receipt.download');

            // Customers — shared: staff need this to run the Jobs screen,
            // but per-customer job/revenue visibility is still scoped
            // inside the controller for non-admin logins.
            Route::get('customers', [AdminCustomerController::class, 'index'])->name('customers.index');
            Route::post('customers', [AdminCustomerController::class, 'store'])->name('customers.store');
            Route::get('customers/{customer}', [AdminCustomerController::class, 'show'])->name('customers.show');
            Route::put('customers/{customer}', [AdminCustomerController::class, 'update'])->name('customers.update');
            Route::delete('customers/{customer}', [AdminCustomerController::class, 'destroy'])->name('customers.destroy');

            // My Account — every logged-in user (admin or staff) manages their own login here.
            Route::get('account', [AdminUserController::class, 'account'])->name('account');
            Route::put('account', [AdminUserController::class, 'updateAccount'])->name('account.update');

            // Staff commission — shared route, but the controller scopes results
            // to "only their own" for staff logins and "all staff" for admins.
            Route::get('reports/staff-commission', [AdminReportController::class, 'staffCommission'])->name('reports.staffCommission');
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

            // Gallery
            Route::get('gallery', [AdminGalleryController::class, 'index'])->name('gallery.index');
            Route::post('gallery', [AdminGalleryController::class, 'store'])->name('gallery.store');
            Route::delete('gallery/{galleryItem}', [AdminGalleryController::class, 'destroy'])->name('gallery.destroy');
            Route::post('gallery/categories', [AdminGalleryCategoryController::class, 'store'])->name('gallery.categories.store');
            Route::put('gallery/categories/{galleryCategory}', [AdminGalleryCategoryController::class, 'update'])->name('gallery.categories.update');
            Route::delete('gallery/categories/{galleryCategory}', [AdminGalleryCategoryController::class, 'destroy'])->name('gallery.categories.destroy');

            // Appointments
            Route::get('appointments', [AdminAppointmentController::class, 'index'])->name('appointments.index');
            Route::get('appointments/calendar', [AdminAppointmentController::class, 'calendar'])->name('appointments.calendar');
            Route::patch('appointments/{appointment}/status', [AdminAppointmentController::class, 'updateStatus'])->name('appointments.status');
            Route::patch('appointments/{appointment}/staff', [AdminAppointmentController::class, 'assignStaff'])->name('appointments.assignStaff');
            Route::delete('appointments/{appointment}', [AdminAppointmentController::class, 'destroy'])->name('appointments.destroy');

            // Wedding Albums (multipart create/update — same "_method": "PUT"
            // spoofing convention as Products for update requests).
            Route::get('albums', [AdminAlbumController::class, 'index'])->name('albums.index');
            Route::get('albums/{album}', [AdminAlbumController::class, 'show'])->name('albums.show');
            Route::post('albums', [AdminAlbumController::class, 'store'])->name('albums.store');
            Route::put('albums/{album}', [AdminAlbumController::class, 'update'])->name('albums.update');
            Route::delete('albums/{album}', [AdminAlbumController::class, 'destroy'])->name('albums.destroy');
            Route::delete('albums/{album}/photos/{photo}', [AdminAlbumController::class, 'destroyPhoto'])->name('albums.photos.destroy');

            // Staff profiles
            Route::get('staff', [AdminStaffController::class, 'index'])->name('staff.index');
            Route::post('staff', [AdminStaffController::class, 'store'])->name('staff.store');
            // 'roster' must come before the /staff/{staff}/* wildcard routes
            // it'd otherwise never be reached (Laravel matches route
            // definitions in order; {staff} would swallow "roster" as an id).
            Route::get('staff/roster', [AdminStaffShiftController::class, 'roster'])->name('staff.roster');
            Route::put('staff/{staff}', [AdminStaffController::class, 'update'])->name('staff.update');
            Route::delete('staff/{staff}', [AdminStaffController::class, 'destroy'])->name('staff.destroy');
            Route::post('staff/{staff}/toggle-active', [AdminStaffController::class, 'toggleActive'])->name('staff.toggleActive');
            Route::get('staff/{staff}/services', [AdminStaffController::class, 'services'])->name('staff.services');
            Route::put('staff/{staff}/services', [AdminStaffController::class, 'syncServices'])->name('staff.services.sync');
            Route::get('staff/{staff}/performance', [AdminStaffController::class, 'performance'])->name('staff.performance');

            // Staff shifts/leave — SALON-OPS-ENHANCEMENTS.md, "Staff"
            Route::get('staff-shifts', [AdminStaffShiftController::class, 'index'])->name('staffShifts.index');
            Route::post('staff-shifts', [AdminStaffShiftController::class, 'store'])->name('staffShifts.store');
            Route::put('staff-shifts/{shift}', [AdminStaffShiftController::class, 'update'])->name('staffShifts.update');
            Route::delete('staff-shifts/{shift}', [AdminStaffShiftController::class, 'destroy'])->name('staffShifts.destroy');

            // Testimonials
            Route::get('testimonials', [AdminTestimonialController::class, 'index'])->name('testimonials.index');
            Route::patch('testimonials/{testimonial}/status', [AdminTestimonialController::class, 'updateStatus'])->name('testimonials.status');
            Route::delete('testimonials/{testimonial}', [AdminTestimonialController::class, 'destroy'])->name('testimonials.destroy');

            // Orders (PDF invoice preview/download need the Bearer token —
            // fetch as a blob client-side, same pattern as the Jobs receipt).
            Route::get('orders', [AdminOrderController::class, 'index'])->name('orders.index');
            Route::get('orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
            Route::patch('orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status');
            Route::post('orders/{order}/mark-paid', [AdminOrderController::class, 'markPaid'])->name('orders.markPaid');
            Route::get('orders/{order}/invoice', [AdminOrderController::class, 'invoicePreview'])->name('orders.invoice.preview');
            Route::get('orders/{order}/invoice/download', [AdminOrderController::class, 'invoiceDownload'])->name('orders.invoice.download');

            // Contact messages
            Route::get('messages', [AdminContactMessageController::class, 'index'])->name('messages.index');
            Route::post('messages/{contactMessage}/read', [AdminContactMessageController::class, 'markRead'])->name('messages.read');
            Route::post('messages/{contactMessage}/replied', [AdminContactMessageController::class, 'markReplied'])->name('messages.replied');
            Route::delete('messages/{contactMessage}', [AdminContactMessageController::class, 'destroy'])->name('messages.destroy');

            // Reports (staff-commission lives in the shared group above —
            // it's the one report staff can also pull, scoped to themselves).
            Route::get('reports/revenue', [AdminReportController::class, 'revenue'])->name('reports.revenue');
            Route::get('reports/best-sellers', [AdminReportController::class, 'bestSellers'])->name('reports.bestSellers');
            Route::get('reports/low-stock', [AdminReportController::class, 'lowStock'])->name('reports.lowStock');
            Route::get('reports/appointments', [AdminReportController::class, 'appointments'])->name('reports.appointments');
            Route::get('reports/outstanding-balances', [AdminReportController::class, 'outstandingBalances'])->name('reports.outstandingBalances');

            // Activity Log
            Route::get('activity-log', [AdminActivityLogController::class, 'index'])->name('activity-log.index');

            // Users — managing other admin/staff accounts (My Account,
            // above, is separate and open to every role).
            Route::get('users', [AdminUserController::class, 'index'])->name('users.index');
            Route::get('users/unlinked-staff', [AdminUserController::class, 'unlinkedStaff'])->name('users.unlinkedStaff');
            Route::post('users', [AdminUserController::class, 'store'])->name('users.store');
            Route::get('users/{user}', [AdminUserController::class, 'show'])->name('users.show');
            Route::put('users/{user}', [AdminUserController::class, 'update'])->name('users.update');
            Route::delete('users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
        });
    });
});
