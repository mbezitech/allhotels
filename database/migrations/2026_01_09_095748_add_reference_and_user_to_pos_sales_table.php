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
        Schema::table('pos_sales', function (Blueprint $table) {
            $table->string('sale_reference')->unique()->nullable()->after('id'); // Unique sale reference
            $table->foreignId('user_id')->nullable()->after('room_id')->constrained('users')->onDelete('set null'); // User who created the sale
            $table->index('sale_reference');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pos_sales', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['sale_reference']);
            $table->dropIndex(['user_id']);
            $table->dropColumn(['sale_reference', 'user_id']);
        });
    }
};
