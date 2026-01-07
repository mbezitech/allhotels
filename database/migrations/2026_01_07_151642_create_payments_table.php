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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->onDelete('cascade');
            $table->foreignId('booking_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('pos_sale_id')->nullable()->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', ['cash', 'card', 'transfer', 'other'])->default('cash');
            $table->string('reference_number')->nullable();
            $table->timestamp('paid_at');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Note: Validation ensures payment is for either booking or POS sale, but not both
            // This is enforced at the application level in PaymentController
            
            // Indexes for performance
            $table->index('hotel_id');
            $table->index('booking_id');
            $table->index('pos_sale_id');
            $table->index('paid_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
