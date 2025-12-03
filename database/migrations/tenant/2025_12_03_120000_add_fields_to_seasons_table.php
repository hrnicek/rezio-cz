<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seasons', function (Blueprint $table) {
            $table->integer('min_persons')->default(1)->after('min_stay');
            $table->boolean('is_full_season_booking_only')->default(false)->after('check_in_days');
            $table->boolean('is_recurring')->default(false)->after('is_fixed_range');
        });
    }

    public function down(): void
    {
        Schema::table('seasons', function (Blueprint $table) {
            $table->dropColumn(['min_persons', 'is_full_season_booking_only', 'is_recurring']);
        });
    }
};
