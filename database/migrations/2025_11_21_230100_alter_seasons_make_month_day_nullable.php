<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Make legacy columns nullable so inserts using start_date/end_date succeed
        DB::statement("ALTER TABLE seasons MODIFY start_month_day VARCHAR(10) NULL");
        DB::statement("ALTER TABLE seasons MODIFY end_month_day VARCHAR(10) NULL");
    }

    public function down(): void
    {
        // Revert to NOT NULL with a safe default if needed
        DB::statement("ALTER TABLE seasons MODIFY start_month_day VARCHAR(10) NOT NULL DEFAULT '01-01'");
        DB::statement("ALTER TABLE seasons MODIFY end_month_day VARCHAR(10) NOT NULL DEFAULT '12-31'");
    }
};

