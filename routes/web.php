<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExtraController;
use App\Http\Controllers\HotelAreaController;
use App\Http\Controllers\HotelController;
use App\Http\Controllers\HousekeepingRecordController;
use App\Http\Controllers\HousekeepingReportController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PosSaleController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserRoleController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function () {
    return redirect()->route('login');
});

// Public booking routes (no authentication required)
Route::get('/book/{hotel_slug}/{room_id}', [\App\Http\Controllers\PublicBookingController::class, 'show'])->name('public.booking.show');
Route::post('/book/{hotel_slug}/{room_id}', [\App\Http\Controllers\PublicBookingController::class, 'store'])->name('public.booking.store');
Route::get('/book/{hotel_slug}/confirmation/{booking_reference}', [\App\Http\Controllers\PublicBookingController::class, 'confirmation'])->name('public.booking.confirmation');
Route::post('/book/{hotel_slug}/{room_id}/check-availability', [\App\Http\Controllers\PublicBookingController::class, 'checkAvailability'])->name('public.booking.check-availability');

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
    
    // Housekeeping Records Management
    Route::middleware('permission:housekeeping.view')->group(function () {
        Route::get('/housekeeping-records', [HousekeepingRecordController::class, 'index'])->name('housekeeping-records.index');
    });
    
    Route::middleware('permission:housekeeping.manage')->group(function () {
        Route::get('/housekeeping-records/create', [HousekeepingRecordController::class, 'create'])->name('housekeeping-records.create');
        Route::post('/housekeeping-records', [HousekeepingRecordController::class, 'store'])->name('housekeeping-records.store');
        Route::post('/housekeeping-records/{housekeepingRecord}/start', [HousekeepingRecordController::class, 'startCleaning'])->name('housekeeping-records.start');
        Route::post('/housekeeping-records/{housekeepingRecord}/complete', [HousekeepingRecordController::class, 'completeCleaning'])->name('housekeeping-records.complete');
        Route::post('/housekeeping-records/{housekeepingRecord}/inspect', [HousekeepingRecordController::class, 'inspectCleaning'])->name('housekeeping-records.inspect');
    });
    
    Route::middleware('permission:housekeeping.view')->group(function () {
        Route::get('/housekeeping-records/{housekeepingRecord}', [HousekeepingRecordController::class, 'show'])->name('housekeeping-records.show');
    });
    
    Route::middleware('permission:housekeeping.manage')->group(function () {
        Route::get('/housekeeping-records/{housekeepingRecord}/edit', [HousekeepingRecordController::class, 'edit'])->name('housekeeping-records.edit');
        Route::put('/housekeeping-records/{housekeepingRecord}', [HousekeepingRecordController::class, 'update'])->name('housekeeping-records.update');
        Route::delete('/housekeeping-records/{housekeepingRecord}', [HousekeepingRecordController::class, 'destroy'])->name('housekeeping-records.destroy');
    });

    // Hotel Areas Management
    Route::middleware('permission:housekeeping.view')->group(function () {
        Route::get('/hotel-areas', [HotelAreaController::class, 'index'])->name('hotel-areas.index');
    });
    
    Route::middleware('permission:housekeeping.manage')->group(function () {
        Route::get('/hotel-areas/create', [HotelAreaController::class, 'create'])->name('hotel-areas.create');
        Route::post('/hotel-areas', [HotelAreaController::class, 'store'])->name('hotel-areas.store');
        Route::get('/hotel-areas/{hotelArea}/edit', [HotelAreaController::class, 'edit'])->name('hotel-areas.edit');
        Route::put('/hotel-areas/{hotelArea}', [HotelAreaController::class, 'update'])->name('hotel-areas.update');
        Route::delete('/hotel-areas/{hotelArea}', [HotelAreaController::class, 'destroy'])->name('hotel-areas.destroy');
    });
    
    Route::middleware('permission:housekeeping.view')->group(function () {
        Route::get('/hotel-areas/{hotelArea}', [HotelAreaController::class, 'show'])->name('hotel-areas.show');
    });

    // Housekeeping Reports
    Route::middleware('permission:housekeeping.view')->group(function () {
        Route::get('/housekeeping-reports', [HousekeepingReportController::class, 'index'])->name('housekeeping-reports.index');
        Route::get('/housekeeping-reports/daily-summary', [HousekeepingReportController::class, 'dailySummary'])->name('housekeeping-reports.daily-summary');
        Route::get('/housekeeping-reports/staff-performance', [HousekeepingReportController::class, 'staffPerformance'])->name('housekeeping-reports.staff-performance');
        Route::get('/housekeeping-reports/pending-tasks', [HousekeepingReportController::class, 'pendingTasks'])->name('housekeeping-reports.pending-tasks');
        Route::get('/housekeeping-reports/issues', [HousekeepingReportController::class, 'issuesReport'])->name('housekeeping-reports.issues');
    });

    // Tasks Management (Housekeeping & Maintenance)
    Route::middleware('permission:housekeeping.view')->group(function () {
        Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
    });
    
    // Tasks create must come before tasks/{task} to avoid route conflict
    Route::middleware('permission:housekeeping.manage')->group(function () {
        Route::get('/tasks/create', [TaskController::class, 'create'])->name('tasks.create');
        Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    });
    
    // Tasks view/show (requires housekeeping.view permission)
    Route::middleware('permission:housekeeping.view')->group(function () {
        Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
    });
    
    // Tasks edit/update/delete (requires housekeeping.manage permission)
    Route::middleware('permission:housekeeping.manage')->group(function () {
        Route::get('/tasks/{task}/edit', [TaskController::class, 'edit'])->name('tasks.edit');
        Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
        Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
    });
    
    // Extra Categories Management (requires stock.manage permission)
    Route::middleware('permission:stock.manage')->group(function () {
        Route::get('/extra-categories', [\App\Http\Controllers\ExtraCategoryController::class, 'index'])->name('extra-categories.index');
        Route::get('/extra-categories/create', [\App\Http\Controllers\ExtraCategoryController::class, 'create'])->name('extra-categories.create');
        Route::post('/extra-categories', [\App\Http\Controllers\ExtraCategoryController::class, 'store'])->name('extra-categories.store');
        Route::get('/extra-categories/{extraCategory}', [\App\Http\Controllers\ExtraCategoryController::class, 'show'])->name('extra-categories.show');
        Route::get('/extra-categories/{extraCategory}/edit', [\App\Http\Controllers\ExtraCategoryController::class, 'edit'])->name('extra-categories.edit');
        Route::put('/extra-categories/{extraCategory}', [\App\Http\Controllers\ExtraCategoryController::class, 'update'])->name('extra-categories.update');
        Route::delete('/extra-categories/{extraCategory}', [\App\Http\Controllers\ExtraCategoryController::class, 'destroy'])->name('extra-categories.destroy');
    });
    
    // Extras Management (requires stock.view or stock.manage permission)
    Route::middleware('permission:stock.view')->group(function () {
        Route::get('/extras', [ExtraController::class, 'index'])->name('extras.index');
    });
    
    // Extras create must come before extras/{extra} to avoid route conflict
    Route::middleware('permission:stock.manage')->group(function () {
        Route::get('/extras/create', [ExtraController::class, 'create'])->name('extras.create');
        Route::post('/extras', [ExtraController::class, 'store'])->name('extras.store');
    });
    
    // Extras view/show (requires stock.view permission)
    Route::middleware('permission:stock.view')->group(function () {
        Route::get('/extras/{extra}', [ExtraController::class, 'show'])->name('extras.show');
    });
    
    // Extras edit/update/delete (requires stock.manage permission)
    Route::middleware('permission:stock.manage')->group(function () {
        Route::get('/extras/{extra}/edit', [ExtraController::class, 'edit'])->name('extras.edit');
        Route::put('/extras/{extra}', [ExtraController::class, 'update'])->name('extras.update');
        Route::delete('/extras/{extra}', [ExtraController::class, 'destroy'])->name('extras.destroy');
    });
    
    // POS Sales Management (requires pos.view permission)
    Route::middleware('permission:pos.view')->group(function () {
        Route::get('/pos-sales', [PosSaleController::class, 'index'])->name('pos-sales.index');
    });
    
    // POS Sales create must come before pos-sales/{posSale} to avoid route conflict
    Route::middleware('permission:pos.sell')->group(function () {
        Route::get('/pos-sales/create', [PosSaleController::class, 'create'])->name('pos-sales.create');
        Route::post('/pos-sales', [PosSaleController::class, 'store'])->name('pos-sales.store');
    });
    
    // POS Sales view/show (requires pos.view permission)
    Route::middleware('permission:pos.view')->group(function () {
        Route::get('/pos-sales/{posSale}', [PosSaleController::class, 'show'])->name('pos-sales.show');
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
