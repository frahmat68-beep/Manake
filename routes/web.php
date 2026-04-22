<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OverviewController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ThemeController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\AvailabilityBoardController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DbExplorerController;
use App\Http\Controllers\Admin\StubController as AdminStubController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\EquipmentController as AdminEquipmentController;
use App\Http\Controllers\Admin\ContentController as AdminContentController;
use App\Http\Controllers\Admin\CopywritingController as AdminCopywritingController;
use App\Http\Controllers\Admin\WebsiteSettingsController as AdminWebsiteSettingsController;

/*
|--------------------------------------------------------------------------
| PUBLIC PAGES (NO AUTH)
|--------------------------------------------------------------------------
*/

Route::get('/assets/public/{path}', [AssetController::class, 'public'])
    ->where('path', '.*')
    ->name('assets.public');
Route::get('/assets/media/{path}', [AssetController::class, 'media'])
    ->where('path', '.*')
    ->name('assets.media');

Route::get('/', [CategoryController::class, 'home'])->name('home');

Route::post('/lang/{locale}', [LocaleController::class, 'switch'])->name('lang.switch');
Route::post('/theme/{theme}', [ThemeController::class, 'switch'])->name('theme.switch');

Route::get('/catalog', [EquipmentController::class, 'index'])->name('catalog');
Route::get('/availability-board', [AvailabilityBoardController::class, 'index'])->name('availability.board');
Route::get('/search/suggestions', [EquipmentController::class, 'suggestions'])->name('search.suggestions');
Route::get('/equipments', [PageController::class, 'catalogRedirect'])->name('equipments.index');
Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');

Route::get('/product/{slug}/availability', [EquipmentController::class, 'availability'])->name('product.availability');
Route::get('/product/{slug}', [EquipmentController::class, 'show'])->name('product.show');
Route::get('/equipment/{slug}', [PageController::class, 'equipmentRedirect'])->name('equipment.show');

Route::get('/category/{slug}', [CategoryController::class, 'show'])->name('category.show');

Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/rental-rules', [PageController::class, 'rentalRules'])->name('rental.rules');
// Chatbot Routes
Route::post('/chatbot/message', [ChatbotController::class, 'chat'])->name('chatbot.message')->middleware('throttle:10,1');
Route::post('/chatbot/reset', [ChatbotController::class, 'reset'])->name('chatbot.reset');

Route::middleware('auth.feature')->group(function () {
    Route::get('/cart', [CartController::class, 'show'])->name('cart');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::patch('/cart/{key}/increment', [CartController::class, 'increment'])->name('cart.increment');
    Route::patch('/cart/{key}/decrement', [CartController::class, 'decrement'])->name('cart.decrement');
    Route::patch('/cart/{key}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{key}', [CartController::class, 'remove'])->name('cart.remove');
});

/*
|--------------------------------------------------------------------------
| USER PAGES (AUTH)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'otp'])->group(function () {
    Route::get('/overview', [OverviewController::class, 'redirect'])->name('overview');

    Route::get('/dashboard', [OverviewController::class, 'redirect'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'complete'])->name('profile');
    Route::get('/profile/complete', [ProfileController::class, 'complete'])->name('profile.complete');
    Route::post('/profile/complete', [ProfileController::class, 'storeCompletion'])->name('profile.complete.store');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');

    Route::get('/booking', [OrderController::class, 'index'])->name('booking.index');
    Route::get('/booking/history', [OrderController::class, 'index'])->name('booking.history');
    Route::get('/booking/pay/{order:order_number}', [OrderController::class, 'pay'])->name('booking.pay');
    Route::get('/booking/{order:order_number}', [OrderController::class, 'show'])->name('booking.show');

    Route::get('/settings', [\App\Http\Controllers\SettingsController::class, 'edit'])->name('settings.index');
    Route::post('/settings', [\App\Http\Controllers\SettingsController::class, 'update'])->name('settings.update');

    Route::post('/payments/{order:order_number}/snap-token', [PaymentController::class, 'createSnapToken'])->name('payments.snap-token');
    Route::post('/payments/{order:order_number}/refresh-status', [PaymentController::class, 'refreshStatus'])->name('payments.refresh-status');
    Route::post('/payments/{order:order_number}/damage-fee/snap-token', [PaymentController::class, 'createDamageFeeSnapToken'])->name('payments.damage-fee.snap-token');
    Route::post('/payments/{order:order_number}/damage-fee/refresh-status', [PaymentController::class, 'refreshDamageFeeStatus'])->name('payments.damage-fee.refresh-status');

    Route::get('/account/orders/{order:order_number}', [OrderController::class, 'show'])->name('account.orders.show');
    Route::patch('/account/orders/{order:order_number}/reschedule', [OrderController::class, 'reschedule'])->name('account.orders.reschedule');
    Route::get('/account/orders/{order:order_number}/receipt', [OrderController::class, 'receipt'])
        ->middleware('signed')
        ->name('account.orders.receipt');
    Route::get('/account/orders/{order:order_number}/invoice.pdf', [OrderController::class, 'receiptPdf'])
        ->middleware('signed')
        ->name('account.orders.receipt.pdf');
});

/*
|--------------------------------------------------------------------------
| CHECKOUT (AUTH + PROFILE COMPLETED)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'otp', 'ensure.profile.completed'])->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
});

/*
|--------------------------------------------------------------------------
| ADMIN ROUTES
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->group(function () {
    /* === AUTH === */
    Route::get('/login', [AdminAuthController::class, 'showLogin'])
        ->middleware('throttle:10,1')
        ->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])
        ->middleware('throttle:10,1')
        ->name('login.store');
    Route::post('/logout', [AdminAuthController::class, 'logout'])
        ->middleware('admin.auth')
        ->name('logout');

    /* === PANEL === */
    Route::middleware(['admin.auth'])->group(function () {
        Route::get('/', [AdminStubController::class, 'home'])->name('home');
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::patch('/dashboard/orders/{order}/operational-status', [AdminDashboardController::class, 'updateOperationalStatus'])->name('dashboard.orders.operational-status');
        Route::get('/settings', [AdminWebsiteSettingsController::class, 'edit'])->name('settings.index');
        Route::post('/settings', [AdminWebsiteSettingsController::class, 'update'])->name('settings.update');
        Route::get('/settings/website', [AdminWebsiteSettingsController::class, 'edit'])->name('website.edit');
        Route::post('/settings/website', [AdminWebsiteSettingsController::class, 'update'])->name('website.update');

        Route::resource('categories', AdminCategoryController::class)
            ->except(['show'])
            ->parameters(['categories' => 'slug'])
            ->names('categories');

        Route::resource('equipments', AdminEquipmentController::class)
            ->except(['show'])
            ->parameters(['equipments' => 'slug'])
            ->names('equipments');

        /* === CONTENT === */
        Route::get('/content', [AdminContentController::class, 'index'])->name('content.index');
        Route::post('/content', [AdminContentController::class, 'update'])->name('content.update');
        Route::get('/copy', [AdminCopywritingController::class, 'index'])->name('copy.index');
        Route::get('/copy/{section}', [AdminCopywritingController::class, 'edit'])->name('copy.edit');
        Route::post('/copy/{section}', [AdminCopywritingController::class, 'update'])->name('copy.update');

        /* === DATA MASTER === */
        Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::put('/orders/{order}', [AdminOrderController::class, 'update'])->name('orders.update');

        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');
        Route::post('/users/{user}/reset-password', [AdminUserController::class, 'sendResetLink'])->name('users.reset-password');
        Route::post('/users/{user}/set-password', [AdminUserController::class, 'setPassword'])->name('users.set-password');
    });

    /* === DB EXPLORER (SUPER ADMIN) === */
    Route::middleware(['admin.auth', 'admin.super', 'throttle:60,1'])->group(function () {
        Route::get('/db', [DbExplorerController::class, 'index'])->name('db.index');
        Route::get('/db/{table}', [DbExplorerController::class, 'table'])->name('db.table');
        Route::get('/db/{table}/{record}', [DbExplorerController::class, 'show'])->name('db.show');
        Route::get('/db/{table}/{record}/edit', [DbExplorerController::class, 'edit'])->name('db.edit');
        Route::put('/db/{table}/{record}', [DbExplorerController::class, 'update'])->name('db.update');
    });
});

require __DIR__.'/auth.php';
