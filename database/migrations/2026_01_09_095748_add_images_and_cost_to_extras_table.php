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
        Schema::table('extras', function (Blueprint $table) {
            $table->json('images')->nullable()->after('description'); // Array of image paths
            $table->decimal('cost', 10, 2)->nullable()->after('price'); // Cost price for profit calculation
            $table->index('cost');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('extras', function (Blueprint $table) {
            $table->dropIndex(['cost']);
            $table->dropColumn(['images', 'cost']);
        });
    }
};
