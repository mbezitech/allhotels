<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->onDelete('cascade');
            $table->string('room_number');
            $table->string('room_type')->default('standard'); // standard, deluxe, suite, etc.
            $table->enum('status', ['available', 'occupied', 'maintenance', 'cleaning'])->default('available');
            $table->integer('floor')->nullable();
            $table->integer('capacity')->default(2); // number of guests
            $table->decimal('price_per_night', 10, 2)->default(0);
            $table->text('description')->nullable();
            $table->json('amenities')->nullable(); // e.g., ["wifi", "tv", "ac"]
            $table->timestamps();
            
            // Unique room number per hotel
            $table->unique(['hotel_id', 'room_number']);
            
            // Indexes for performance
            $table->index('hotel_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
