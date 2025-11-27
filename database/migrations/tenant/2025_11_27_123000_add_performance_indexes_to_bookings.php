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
            // Index for basic availability checks
            $table->index(['property_id', 'status', 'start_date', 'end_date'], 'bookings_availability_idx');

            // Index for overlapping date checks (used in availability logic)
            $table->index(['property_id', 'status', 'date_start', 'date_end'], 'bookings_overlap_idx');

            // Index for checkin token lookups
            if (Schema::hasColumn('bookings', 'checkin_token')) {
                $table->index('checkin_token');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('bookings_availability_idx');
            $table->dropIndex('bookings_overlap_idx');
            if (Schema::hasColumn('bookings', 'checkin_token')) {
                $table->dropIndex(['checkin_token']);
            }
        });
    }
};
