<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Public API routes for WordPress integration
Route::prefix('hotels/{hotel_slug}')->group(function () {
    Route::get('/rooms', [\App\Http\Controllers\PublicBookingController::class, 'apiGetRooms']);
    Route::get('/room-types', [\App\Http\Controllers\PublicBookingController::class, 'apiGetRoomTypes']);
    Route::post('/rooms/{room_id}/book', [\App\Http\Controllers\PublicBookingController::class, 'apiStore']);
});
