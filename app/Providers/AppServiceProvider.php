<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Scope booking route model binding to current hotel
        \Illuminate\Support\Facades\Route::bind('booking', function ($value) {
            $hotelId = session('hotel_id');
            if (!$hotelId) {
                abort(403, 'No hotel context set. Please select a hotel to view bookings.');
            }
            $booking = \App\Models\Booking::where('id', $value)
                ->where('hotel_id', $hotelId)
                ->first();
            
            if (!$booking) {
                abort(404, 'Booking not found or does not belong to your hotel.');
            }
            
            return $booking;
        });
    }
}
