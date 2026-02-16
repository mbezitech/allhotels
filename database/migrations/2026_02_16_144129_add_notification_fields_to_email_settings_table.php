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
        Schema::table('email_settings', function (Blueprint $table) {
            $table->boolean('notify_booking')->default(true)->after('notification_email');
            $table->text('booking_notification_email')->nullable()->after('notify_booking');
            
            $table->boolean('notify_cancellation')->default(true)->after('booking_notification_email');
            $table->text('cancellation_notification_email')->nullable()->after('notify_cancellation');
            
            $table->boolean('notify_payment')->default(true)->after('cancellation_notification_email');
            $table->text('payment_notification_email')->nullable()->after('notify_payment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_settings', function (Blueprint $table) {
            $table->dropColumn([
                'notify_booking',
                'booking_notification_email',
                'notify_cancellation',
                'cancellation_notification_email',
                'notify_payment',
                'payment_notification_email'
            ]);
        });
    }
};
