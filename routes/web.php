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
use App\Http\Controllers\LinkController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserRoleController;
use App\Http\Controllers\EmailSettingsController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\ExpenseReportController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function () {
    return redirect()->route('login');
});

// Public booking routes (no authentication required)
Route::get('/search/{hotel_slug}', [\App\Http\Controllers\PublicBookingController::class, 'search'])->name('public.search');
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
    Route::post('/hotels/{id}/restore', [HotelController::class, 'restore'])->name('hotels.restore');
    Route::delete('/hotels/{id}/force-delete', [HotelController::class, 'forceDelete'])->name('hotels.force-delete');
    Route::post('/hotels/switch', [HotelController::class, 'switchHotel'])->name('hotels.switch');
    Route::post('/hotels/{hotel}/switch', [HotelController::class, 'switchHotel'])->name('hotels.switch.hotel');
    
    // User Management (super admin only)
    Route::resource('users', UserController::class);
    Route::put('/users/{user}/activate', [UserController::class, 'activate'])->name('users.activate');
    Route::put('/users/{user}/deactivate', [UserController::class, 'deactivate'])->name('users.deactivate');
    Route::post('/users/{id}/restore', [UserController::class, 'restore'])->name('users.restore');
    Route::delete('/users/{id}/force-delete', [UserController::class, 'forceDelete'])->name('users.forceDelete');
    
    // Profile Management (all authenticated users)
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
});

// Protected routes (require authentication and hotel context)
Route::middleware(['auth', 'hotel.context'])->group(function () {
    Route::middleware('permission:dashboard.view')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    });
    
    // Role Management
    Route::middleware('permission:roles.view')->group(function () {
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    });
    
    // Create route must come before {role} route to avoid route conflict
    Route::middleware('permission:roles.manage')->group(function () {
        Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create');
        Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
    });
    
    Route::middleware('permission:roles.view')->group(function () {
        Route::get('/roles/{role}', [RoleController::class, 'show'])->name('roles.show');
    });
    
    Route::middleware('permission:roles.edit')->group(function () {
        Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
        Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
    });
    
    Route::middleware('permission:roles.manage')->group(function () {
        Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
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
    
    // Rooms edit/update/delete
    Route::middleware('permission:rooms.edit')->group(function () {
        Route::get('/rooms/{room}/edit', [RoomController::class, 'edit'])->name('rooms.edit');
        Route::put('/rooms/{room}', [RoomController::class, 'update'])->name('rooms.update');
    });
    
    Route::middleware('permission:rooms.manage')->group(function () {
        Route::get('/rooms/create', [RoomController::class, 'create'])->name('rooms.create');
        Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');
    });
    
    // Rooms delete (requires rooms.delete permission)
    Route::middleware('permission:rooms.delete')->group(function () {
        Route::delete('/rooms/{room}', [RoomController::class, 'destroy'])->name('rooms.destroy');
        Route::post('/rooms/{id}/restore', [RoomController::class, 'restore'])->name('rooms.restore');
        Route::delete('/rooms/{id}/force-delete', [RoomController::class, 'forceDelete'])->name('rooms.forceDelete');
    });
    
    // Room Types Management
    Route::middleware('permission:room_types.view')->group(function () {
        Route::get('/room-types', [\App\Http\Controllers\RoomTypeController::class, 'index'])->name('room-types.index');
    });
    
    // Create route must come before {roomType} route to avoid route conflict
    Route::middleware('permission:room_types.manage')->group(function () {
        Route::get('/room-types/create', [\App\Http\Controllers\RoomTypeController::class, 'create'])->name('room-types.create');
        Route::post('/room-types', [\App\Http\Controllers\RoomTypeController::class, 'store'])->name('room-types.store');
    });
    
    Route::middleware('permission:room_types.view')->group(function () {
        Route::get('/room-types/{roomType}', [\App\Http\Controllers\RoomTypeController::class, 'show'])->name('room-types.show');
    });
    
    Route::middleware('permission:room_types.edit')->group(function () {
        Route::get('/room-types/{roomType}/edit', [\App\Http\Controllers\RoomTypeController::class, 'edit'])->name('room-types.edit');
        Route::put('/room-types/{roomType}', [\App\Http\Controllers\RoomTypeController::class, 'update'])->name('room-types.update');
    });
    
    Route::middleware('permission:room_types.manage')->group(function () {
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
    
    // Bookings check-in/check-out actions (requires bookings.edit permission)
    Route::middleware('permission:bookings.edit')->group(function () {
        Route::post('/bookings/{booking}/check-in', [BookingController::class, 'checkIn'])->name('bookings.check-in');
        Route::post('/bookings/{booking}/check-out', [BookingController::class, 'checkOut'])->name('bookings.check-out');
    });
    
    Route::middleware('permission:bookings.edit')->group(function () {
        Route::get('/bookings/{booking}/edit', [BookingController::class, 'edit'])->name('bookings.edit');
        Route::put('/bookings/{booking}', [BookingController::class, 'update'])->name('bookings.update');
    });
    
    Route::middleware('permission:bookings.delete')->group(function () {
        Route::delete('/bookings/{booking}', [BookingController::class, 'destroy'])->name('bookings.destroy');
        Route::post('/bookings/{id}/restore', [BookingController::class, 'restore'])->name('bookings.restore');
        Route::delete('/bookings/{id}/force-delete', [BookingController::class, 'forceDelete'])->name('bookings.forceDelete');
    });
    
    // Housekeeping Records Management
    Route::middleware('permission:housekeeping_records.view')->group(function () {
        Route::get('/housekeeping-records', [HousekeepingRecordController::class, 'index'])->name('housekeeping-records.index');
    });
    
    // Create route must come before {housekeepingRecord} route to avoid route conflict
    Route::middleware('permission:housekeeping_records.manage')->group(function () {
        Route::get('/housekeeping-records/create', [HousekeepingRecordController::class, 'create'])->name('housekeeping-records.create');
        Route::post('/housekeeping-records', [HousekeepingRecordController::class, 'store'])->name('housekeeping-records.store');
    });
    
    Route::middleware('permission:housekeeping_records.view')->group(function () {
        Route::get('/housekeeping-records/{housekeepingRecord}', [HousekeepingRecordController::class, 'show'])->name('housekeeping-records.show');
    });
    
    Route::middleware('permission:housekeeping_records.manage')->group(function () {
        Route::post('/housekeeping-records/{housekeepingRecord}/start', [HousekeepingRecordController::class, 'startCleaning'])->name('housekeeping-records.start');
        Route::post('/housekeeping-records/{housekeepingRecord}/complete', [HousekeepingRecordController::class, 'completeCleaning'])->name('housekeeping-records.complete');
        Route::delete('/housekeeping-records/{housekeepingRecord}', [HousekeepingRecordController::class, 'destroy'])->name('housekeeping-records.destroy');
    });
    
    // Resolve issues requires specific permission
    Route::middleware('permission:housekeeping_records.resolve')->group(function () {
        Route::post('/housekeeping-records/{housekeepingRecord}/resolve', [HousekeepingRecordController::class, 'resolveIssue'])->name('housekeeping-records.resolve');
    });
    
    // Inspect requires specific permission
    Route::middleware('permission:housekeeping_records.inspect')->group(function () {
        Route::post('/housekeeping-records/{housekeepingRecord}/inspect', [HousekeepingRecordController::class, 'inspectCleaning'])->name('housekeeping-records.inspect');
    });
    
    Route::middleware('permission:housekeeping_records.edit')->group(function () {
        Route::get('/housekeeping-records/{housekeepingRecord}/edit', [HousekeepingRecordController::class, 'edit'])->name('housekeeping-records.edit');
        Route::put('/housekeeping-records/{housekeepingRecord}', [HousekeepingRecordController::class, 'update'])->name('housekeeping-records.update');
    });

    // Hotel Areas Management
    Route::middleware('permission:hotel_areas.view')->group(function () {
        Route::get('/hotel-areas', [HotelAreaController::class, 'index'])->name('hotel-areas.index');
    });
    
    Route::middleware('permission:hotel_areas.manage')->group(function () {
        Route::get('/hotel-areas/create', [HotelAreaController::class, 'create'])->name('hotel-areas.create');
        Route::post('/hotel-areas', [HotelAreaController::class, 'store'])->name('hotel-areas.store');
    });
    
    Route::middleware('permission:hotel_areas.view')->group(function () {
        Route::get('/hotel-areas/{hotelArea}', [HotelAreaController::class, 'show'])->name('hotel-areas.show');
    });
    
    Route::middleware('permission:hotel_areas.edit')->group(function () {
        Route::get('/hotel-areas/{hotelArea}/edit', [HotelAreaController::class, 'edit'])->name('hotel-areas.edit');
        Route::put('/hotel-areas/{hotelArea}', [HotelAreaController::class, 'update'])->name('hotel-areas.update');
    });
    
    Route::middleware('permission:hotel_areas.manage')->group(function () {
        Route::delete('/hotel-areas/{hotelArea}', [HotelAreaController::class, 'destroy'])->name('hotel-areas.destroy');
    });

    // Housekeeping Reports
    Route::middleware('permission:housekeeping_reports.view')->group(function () {
        Route::get('/housekeeping-reports', [HousekeepingReportController::class, 'index'])->name('housekeeping-reports.index');
        Route::get('/housekeeping-reports/daily-summary', [HousekeepingReportController::class, 'dailySummary'])->name('housekeeping-reports.daily-summary');
        Route::get('/housekeeping-reports/staff-performance', [HousekeepingReportController::class, 'staffPerformance'])->name('housekeeping-reports.staff-performance');
        Route::get('/housekeeping-reports/pending-tasks', [HousekeepingReportController::class, 'pendingTasks'])->name('housekeeping-reports.pending-tasks');
        Route::get('/housekeeping-reports/issues', [HousekeepingReportController::class, 'issuesReport'])->name('housekeeping-reports.issues');
    });

    // Link References (public links for the current hotel)
    Route::middleware('permission:rooms.view')->group(function () {
        Route::get('/links', [LinkController::class, 'index'])->name('links.index');
    });

    // Tasks Management (Housekeeping & Maintenance)
    Route::middleware('permission:tasks.view')->group(function () {
        Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
    });
    
    // Tasks create must come before tasks/{task} to avoid route conflict
    // Tasks create (requires tasks.create permission)
    Route::middleware('permission:tasks.create')->group(function () {
        Route::get('/tasks/create', [TaskController::class, 'create'])->name('tasks.create');
        Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    });
    
    Route::middleware('permission:tasks.view')->group(function () {
        Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
    });
    
    // Tasks edit/update (requires tasks.edit permission)
    Route::middleware('permission:tasks.edit')->group(function () {
        Route::get('/tasks/{task}/edit', [TaskController::class, 'edit'])->name('tasks.edit');
        Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    });
    
    // Tasks delete (requires tasks.manage permission)
    Route::middleware('permission:tasks.manage')->group(function () {
        Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
    });
    
    // Extra Categories Management
    Route::middleware('permission:extra_categories.view')->group(function () {
        Route::get('/extra-categories', [\App\Http\Controllers\ExtraCategoryController::class, 'index'])->name('extra-categories.index');
    });
    
    Route::middleware('permission:extra_categories.manage')->group(function () {
        Route::get('/extra-categories/create', [\App\Http\Controllers\ExtraCategoryController::class, 'create'])->name('extra-categories.create');
        Route::post('/extra-categories', [\App\Http\Controllers\ExtraCategoryController::class, 'store'])->name('extra-categories.store');
    });
    
    Route::middleware('permission:extra_categories.view')->group(function () {
        Route::get('/extra-categories/{extraCategory}', [\App\Http\Controllers\ExtraCategoryController::class, 'show'])->name('extra-categories.show');
    });
    
    Route::middleware('permission:extra_categories.edit')->group(function () {
        Route::get('/extra-categories/{extraCategory}/edit', [\App\Http\Controllers\ExtraCategoryController::class, 'edit'])->name('extra-categories.edit');
        Route::put('/extra-categories/{extraCategory}', [\App\Http\Controllers\ExtraCategoryController::class, 'update'])->name('extra-categories.update');
    });
    
    Route::middleware('permission:extra_categories.manage')->group(function () {
        Route::delete('/extra-categories/{extraCategory}', [\App\Http\Controllers\ExtraCategoryController::class, 'destroy'])->name('extra-categories.destroy');
    });
    
    // Extras Management
    Route::middleware('permission:extras.view')->group(function () {
        Route::get('/extras', [ExtraController::class, 'index'])->name('extras.index');
    });
    
    // Extras create must come before extras/{extra} to avoid route conflict
    Route::middleware('permission:extras.manage')->group(function () {
        Route::get('/extras/create', [ExtraController::class, 'create'])->name('extras.create');
        Route::post('/extras', [ExtraController::class, 'store'])->name('extras.store');
    });
    
    Route::middleware('permission:extras.view')->group(function () {
        Route::get('/extras/{extra}', [ExtraController::class, 'show'])->name('extras.show');
    });
    
    // Extras edit/update (requires extras.edit permission)
    Route::middleware('permission:extras.edit')->group(function () {
        Route::get('/extras/{extra}/edit', [ExtraController::class, 'edit'])->name('extras.edit');
        Route::put('/extras/{extra}', [ExtraController::class, 'update'])->name('extras.update');
    });
    
    Route::middleware('permission:extras.manage')->group(function () {
        Route::delete('/extras/{extra}', [ExtraController::class, 'destroy'])->name('extras.destroy');
        Route::post('/extras/{id}/restore', [ExtraController::class, 'restore'])->name('extras.restore');
        Route::delete('/extras/{id}/force-delete', [ExtraController::class, 'forceDelete'])->name('extras.forceDelete');
    });
    
    // POS Sales - Create route must come before parameterized routes to avoid route conflict
    Route::middleware('permission:pos.sell')->group(function () {
        Route::get('/pos-sales/create', [PosSaleController::class, 'create'])->name('pos-sales.create');
        Route::post('/pos-sales', [PosSaleController::class, 'store'])->name('pos-sales.store');
    });
    
    // POS Sales Management
    Route::middleware('permission:pos.view')->group(function () {
        Route::get('/pos-sales', [PosSaleController::class, 'index'])->name('pos-sales.index');
        Route::get('/pos-sales/{posSale}', [PosSaleController::class, 'show'])->name('pos-sales.show');
    });
    
    // POS Sales edit/update/delete (permissions available for future implementation)
    // Route::middleware('permission:pos.edit')->group(function () {
    //     Route::get('/pos-sales/{posSale}/edit', [PosSaleController::class, 'edit'])->name('pos-sales.edit');
    //     Route::put('/pos-sales/{posSale}', [PosSaleController::class, 'update'])->name('pos-sales.update');
    // });
    // 
    // Route::middleware('permission:pos.delete')->group(function () {
    //     Route::delete('/pos-sales/{posSale}', [PosSaleController::class, 'destroy'])->name('pos-sales.destroy');
    // });
    
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
    
    // Payments edit/update (permissions available for future implementation)
    // Route::middleware('permission:payments.edit')->group(function () {
    //     Route::get('/payments/{payment}/edit', [PaymentController::class, 'edit'])->name('payments.edit');
    //     Route::put('/payments/{payment}', [PaymentController::class, 'update'])->name('payments.update');
    // });
    
    Route::middleware('permission:payments.delete')->group(function () {
        Route::delete('/payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');
        Route::post('/payments/{id}/restore', [PaymentController::class, 'restore'])->name('payments.restore');
        Route::delete('/payments/{id}/force-delete', [PaymentController::class, 'forceDelete'])->name('payments.forceDelete');
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
    
    // Email Settings (requires email_settings.view permission)
    Route::middleware('permission:email_settings.view')->group(function () {
        Route::get('/email-settings', [EmailSettingsController::class, 'index'])->name('email-settings.index');
    });
    
    // Email Settings management (requires email_settings.manage permission)
    Route::middleware('permission:email_settings.manage')->group(function () {
        Route::post('/email-settings', [EmailSettingsController::class, 'store'])->name('email-settings.store');
        Route::post('/email-settings/test-email', [EmailSettingsController::class, 'sendTestEmail'])->name('email-settings.test-email');
    });
    
    // Expenses - Create route must come before parameterized routes
    Route::middleware('permission:expenses.create')->group(function () {
        Route::get('/expenses/create', [ExpenseController::class, 'create'])->name('expenses.create');
        Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
    });
    
    // Expenses (requires expenses.view permission)
    Route::middleware('permission:expenses.view')->group(function () {
        Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
        Route::get('/expenses/{expense}', [ExpenseController::class, 'show'])->name('expenses.show');
    });
    
    // Expenses edit (requires expenses.edit permission)
    Route::middleware('permission:expenses.edit')->group(function () {
        Route::get('/expenses/{expense}/edit', [ExpenseController::class, 'edit'])->name('expenses.edit');
        Route::put('/expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update');
    });
    
    // Expenses delete (requires expenses.delete permission)
    Route::middleware('permission:expenses.delete')->group(function () {
        Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');
        Route::post('/expenses/{id}/restore', [ExpenseController::class, 'restore'])->name('expenses.restore');
        Route::delete('/expenses/{id}/force-delete', [ExpenseController::class, 'forceDelete'])->name('expenses.forceDelete');
    });
    
    // Expense Categories (requires expense_categories.view permission)
    Route::middleware('permission:expense_categories.view')->group(function () {
        Route::get('/expense-categories', [ExpenseCategoryController::class, 'index'])->name('expense-categories.index');
    });
    
    // Expense Categories management (requires expense_categories.manage permission)
    Route::middleware('permission:expense_categories.manage')->group(function () {
        Route::get('/expense-categories/create', [ExpenseCategoryController::class, 'create'])->name('expense-categories.create');
        Route::post('/expense-categories', [ExpenseCategoryController::class, 'store'])->name('expense-categories.store');
        Route::delete('/expense-categories/{expenseCategory}', [ExpenseCategoryController::class, 'destroy'])->name('expense-categories.destroy');
    });
    
    // Expense Categories edit (requires expense_categories.edit permission)
    Route::middleware('permission:expense_categories.edit')->group(function () {
        Route::get('/expense-categories/{expenseCategory}/edit', [ExpenseCategoryController::class, 'edit'])->name('expense-categories.edit');
        Route::put('/expense-categories/{expenseCategory}', [ExpenseCategoryController::class, 'update'])->name('expense-categories.update');
    });
    
    // Expense Reports (requires expense_reports.view permission)
    Route::middleware('permission:expense_reports.view')->group(function () {
        Route::get('/expense-reports', [ExpenseReportController::class, 'index'])->name('expense-reports.index');
        Route::get('/expense-reports/export', [ExpenseReportController::class, 'export'])->name('expense-reports.export');
    });
});
