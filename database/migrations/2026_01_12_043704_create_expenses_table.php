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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->onDelete('cascade');
            $table->date('expense_date');
            $table->foreignId('expense_category_id')->constrained('expense_categories')->onDelete('restrict');
            $table->text('description');
            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', ['cash', 'bank', 'mobile'])->default('cash');
            $table->foreignId('added_by')->constrained('users')->onDelete('restrict');
            $table->string('attachment')->nullable();
            $table->timestamps();
            
            $table->index('hotel_id');
            $table->index('expense_date');
            $table->index('expense_category_id');
            $table->index('payment_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
