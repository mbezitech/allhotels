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
        Schema::create('room_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->onDelete('cascade');
            $table->string('name'); // e.g., "Standard", "Deluxe", "Suite"
            $table->string('slug')->unique(); // e.g., "standard", "deluxe", "suite"
            $table->text('description')->nullable();
            $table->decimal('base_price', 10, 2)->default(0);
            $table->integer('default_capacity')->default(2);
            $table->json('amenities')->nullable(); // Default amenities for this room type
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Unique name per hotel
            $table->unique(['hotel_id', 'name']);
            $table->index('hotel_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_types');
    }
};
