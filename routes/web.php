<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExtraController;
use App\Http\Controllers\HotelController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PosSaleController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\UserRoleController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Hotel Management (super admin only - accessible without hotel context)
// Access control is handled in HotelController constructor
Route::middleware('auth')->group(function () {
    Route::resource('hotels', HotelController::class);
});

// Protected routes (require authentication and hotel context)
Route::middleware(['auth', 'hotel.context'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Role Management (requires roles.manage permission)
    Route::middleware('permission:roles.manage')->group(function () {
        Route::resource('roles', RoleController::class);
    });
    
    // User Role Assignment (requires roles.manage permission)
    Route::middleware('permission:roles.manage')->group(function () {
        Route::get('/user-roles', [UserRoleController::class, 'create'])->name('user-roles.create');
        Route::post('/user-roles', [UserRoleController::class, 'store'])->name('user-roles.store');
        Route::delete('/user-roles/{user}/{role}', [UserRoleController::class, 'destroy'])->name('user-roles.destroy');
    });
    
    // Rooms Management
    Route::middleware('permission:rooms.view')->group(function () {
        Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');
    });
    
    // Rooms create must come before rooms/{room} to avoid route conflict
    Route::middleware('permission:rooms.manage')->group(function () {
        Route::get('/rooms/create', [RoomController::class, 'create'])->name('rooms.create');
        Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');
    });
    
    // Rooms view/show (requires rooms.view permission)
    Route::middleware('permission:rooms.view')->group(function () {
        Route::get('/rooms/{room}', [RoomController::class, 'show'])->name('rooms.show');
    });
    
    // Rooms edit/update/delete (requires rooms.manage permission)
    Route::middleware('permission:rooms.manage')->group(function () {
        Route::get('/rooms/{room}/edit', [RoomController::class, 'edit'])->name('rooms.edit');
        Route::put('/rooms/{room}', [RoomController::class, 'update'])->name('rooms.update');
        Route::delete('/rooms/{room}', [RoomController::class, 'destroy'])->name('rooms.destroy');
    });
    
    // Room Types Management (requires rooms.manage permission)
    Route::middleware('permission:rooms.manage')->group(function () {
        Route::get('/room-types', [\App\Http\Controllers\RoomTypeController::class, 'index'])->name('room-types.index');
        Route::get('/room-types/create', [\App\Http\Controllers\RoomTypeController::class, 'create'])->name('room-types.create');
        Route::post('/room-types', [\App\Http\Controllers\RoomTypeController::class, 'store'])->name('room-types.store');
        Route::get('/room-types/{roomType}', [\App\Http\Controllers\RoomTypeController::class, 'show'])->name('room-types.show');
        Route::get('/room-types/{roomType}/edit', [\App\Http\Controllers\RoomTypeController::class, 'edit'])->name('room-types.edit');
        Route::put('/room-types/{roomType}', [\App\Http\Controllers\RoomTypeController::class, 'update'])->name('room-types.update');
        Route::delete('/room-types/{roomType}', [\App\Http\Controllers\RoomTypeController::class, 'destroy'])->name('room-types.destroy');
    });
    
    // Bookings Management
    Route::middleware('permission:bookings.view')->group(function () {
        Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
        Route::get('/bookings/calendar', [BookingController::class, 'calendar'])->name('bookings.calendar');
    });
    
    // Bookings create must come before bookings/{booking} to avoid route conflict
    Route::middleware('permission:bookings.create')->group(function () {
        Route::get('/bookings/create', [BookingController::class, 'create'])->name('bookings.create');
        Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
    });
    
    // Bookings view/show (requires bookings.view permission)
    Route::middleware('permission:bookings.view')->group(function () {
        Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    });
    
    Route::middleware('permission:bookings.edit')->group(function () {
        Route::get('/bookings/{booking}/edit', [BookingController::class, 'edit'])->name('bookings.edit');
        Route::put('/bookings/{booking}', [BookingController::class, 'update'])->name('bookings.update');
    });
    
    Route::middleware('permission:bookings.delete')->group(function () {
        Route::delete('/bookings/{booking}', [BookingController::class, 'destroy'])->name('bookings.destroy');
    });
    
    // Extras Management (requires stock.view or stock.manage permission)
    Route::middleware('permission:stock.view')->group(function () {
        Route::get('/extras', [ExtraController::class, 'index'])->name('extras.index');
        Route::get('/extras/{extra}', [ExtraController::class, 'show'])->name('extras.show');
    });
    
    Route::middleware('permission:stock.manage')->group(function () {
        Route::get('/extras/create', [ExtraController::class, 'create'])->name('extras.create');
        Route::post('/extras', [ExtraController::class, 'store'])->name('extras.store');
        Route::get('/extras/{extra}/edit', [ExtraController::class, 'edit'])->name('extras.edit');
        Route::put('/extras/{extra}', [ExtraController::class, 'update'])->name('extras.update');
        Route::delete('/extras/{extra}', [ExtraController::class, 'destroy'])->name('extras.destroy');
    });
    
    // POS Sales Management (requires pos.view permission)
    Route::middleware('permission:pos.view')->group(function () {
        Route::get('/pos-sales', [PosSaleController::class, 'index'])->name('pos-sales.index');
        Route::get('/pos-sales/{posSale}', [PosSaleController::class, 'show'])->name('pos-sales.show');
    });
    
    Route::middleware('permission:pos.sell')->group(function () {
        Route::get('/pos-sales/create', [PosSaleController::class, 'create'])->name('pos-sales.create');
        Route::post('/pos-sales', [PosSaleController::class, 'store'])->name('pos-sales.store');
    });
    
    // Stock Movements Management (requires stock.view or stock.manage permission)
    Route::middleware('permission:stock.view')->group(function () {
        Route::get('/stock-movements', [StockMovementController::class, 'index'])->name('stock-movements.index');
        Route::get('/stock-movements/balance', [StockMovementController::class, 'balance'])->name('stock-movements.balance');
    });
    
    Route::middleware('permission:stock.manage')->group(function () {
        Route::get('/stock-movements/create', [StockMovementController::class, 'create'])->name('stock-movements.create');
        Route::post('/stock-movements', [StockMovementController::class, 'store'])->name('stock-movements.store');
    });
    
    // Payments Management
    Route::middleware('permission:payments.view')->group(function () {
        Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    });
    
    // Payments create must come before payments/{payment} to avoid route conflict
    Route::middleware('permission:payments.create')->group(function () {
        Route::get('/payments/create', [PaymentController::class, 'create'])->name('payments.create');
        Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
    });
    
    // Payments view/show (requires payments.view permission)
    Route::middleware('permission:payments.view')->group(function () {
        Route::get('/payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
    });
    
    Route::middleware('permission:payments.delete')->group(function () {
        Route::delete('/payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');
    });
    
    // Reports (requires reports.view permission)
    Route::middleware('permission:reports.view')->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/daily-sales', [ReportController::class, 'dailySales'])->name('reports.daily-sales');
        Route::get('/reports/occupancy', [ReportController::class, 'occupancy'])->name('reports.occupancy');
        Route::get('/reports/stock', [ReportController::class, 'stock'])->name('reports.stock');
    });
    
    // Activity Logs (requires activity_logs.view permission)
    Route::middleware('permission:activity_logs.view')->group(function () {
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
        Route::get('/activity-logs/{activityLog}', [ActivityLogController::class, 'show'])->name('activity-logs.show');
    });
});
