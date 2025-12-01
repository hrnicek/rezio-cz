<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE services MODIFY COLUMN price_type ENUM('per_person', 'per_night', 'per_day', 'per_stay', 'fixed', 'flat', 'per_hour') NOT NULL DEFAULT 'fixed'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE services MODIFY COLUMN price_type ENUM('per_person', 'per_night', 'per_day', 'fixed') NOT NULL DEFAULT 'fixed'");
    }
};
