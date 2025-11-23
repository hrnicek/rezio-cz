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
        Schema::table('bookings', function (Blueprint $table) {
            $table->uuid('checkin_token')->nullable()->unique()->after('status');
        });

        // Backfill existing bookings
        \App\Models\Booking::chunk(100, function ($bookings) {
            foreach ($bookings as $booking) {
                if (empty($booking->checkin_token)) {
                    $booking->update(['checkin_token' => (string) \Illuminate\Support\Str::uuid()]);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('checkin_token');
        });
    }
};
