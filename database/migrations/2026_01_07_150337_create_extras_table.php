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
        Schema::create('extras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category')->default('general'); // bar, pool, restaurant, general
            $table->decimal('price', 10, 2)->default(0);
            $table->boolean('stock_tracked')->default(false);
            $table->integer('min_stock')->nullable(); // Minimum stock level for alerts
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Indexes
            $table->index('hotel_id');
            $table->index('category');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('extras');
    }
};
