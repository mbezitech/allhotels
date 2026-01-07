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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained('extras')->onDelete('cascade');
            $table->enum('type', ['in', 'out']); // in = stock added, out = stock removed
            $table->integer('quantity');
            $table->string('reference_type')->nullable(); // e.g., 'App\Models\PosSale', 'manual', etc.
            $table->unsignedBigInteger('reference_id')->nullable(); // ID of the reference
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            // Indexes for performance
            $table->index('hotel_id');
            $table->index('product_id');
            $table->index(['reference_type', 'reference_id']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
