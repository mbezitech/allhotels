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
            $user = auth()->user();
            
            // Super admins can access any booking
            if ($user && $user->isSuperAdmin()) {
                $booking = \App\Models\Booking::find($value);
                if (!$booking) {
                    abort(404, 'Booking not found.');
                }
                return $booking;
            }
            
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

        // Scope extraCategory route model binding to current hotel
        \Illuminate\Support\Facades\Route::bind('extraCategory', function ($value) {
            $hotelId = session('hotel_id');
            $user = auth()->user();
            
            // Super admins can access any category
            if ($user && $user->isSuperAdmin()) {
                $category = \App\Models\ExtraCategory::find($value);
                if (!$category) {
                    abort(404, 'Category not found.');
                }
                return $category;
            }
            
            if (!$hotelId) {
                abort(403, 'No hotel context set. Please select a hotel.');
            }
            $category = \App\Models\ExtraCategory::where('id', $value)
                ->where('hotel_id', $hotelId)
                ->first();
            
            if (!$category) {
                abort(404, 'Category not found or does not belong to your hotel.');
            }
            
            return $category;
        });

        // Scope task route model binding to current hotel
        \Illuminate\Support\Facades\Route::bind('task', function ($value) {
            $hotelId = session('hotel_id');
            $user = auth()->user();
            
            // Super admins can access any task
            if ($user && $user->isSuperAdmin()) {
                $task = \App\Models\Task::find($value);
                if (!$task) {
                    abort(404, 'Task not found.');
                }
                return $task;
            }
            
            if (!$hotelId) {
                abort(403, 'No hotel context set. Please select a hotel to view tasks.');
            }
            $task = \App\Models\Task::where('id', $value)
                ->where('hotel_id', $hotelId)
                ->first();
            
            if (!$task) {
                abort(404, 'Task not found or does not belong to your hotel.');
            }
            
            return $task;
        });

        // Scope housekeepingRecord route model binding to current hotel
        \Illuminate\Support\Facades\Route::bind('housekeepingRecord', function ($value) {
            $hotelId = session('hotel_id');
            $user = auth()->user();
            
            // Super admins can access any record
            if ($user && $user->isSuperAdmin()) {
                $record = \App\Models\HousekeepingRecord::find($value);
                if (!$record) {
                    abort(404, 'Housekeeping record not found.');
                }
                return $record;
            }
            
            if (!$hotelId) {
                abort(403, 'No hotel context set. Please select a hotel to view housekeeping records.');
            }
            $record = \App\Models\HousekeepingRecord::where('id', $value)
                ->where('hotel_id', $hotelId)
                ->first();
            
            if (!$record) {
                abort(404, 'Housekeeping record not found or does not belong to your hotel.');
            }
            
            return $record;
        });

        // Scope hotelArea route model binding to current hotel
        \Illuminate\Support\Facades\Route::bind('hotelArea', function ($value) {
            $hotelId = session('hotel_id');
            $user = auth()->user();
            
            // Super admins can access any area
            if ($user && $user->isSuperAdmin()) {
                $area = \App\Models\HotelArea::find($value);
                if (!$area) {
                    abort(404, 'Hotel area not found.');
                }
                return $area;
            }
            
            if (!$hotelId) {
                abort(403, 'No hotel context set. Please select a hotel to manage areas.');
            }
            $area = \App\Models\HotelArea::where('id', $value)
                ->where('hotel_id', $hotelId)
                ->first();
            
            if (!$area) {
                abort(404, 'Hotel area not found or does not belong to your hotel.');
            }
            
            return $area;
        });
    }
}
