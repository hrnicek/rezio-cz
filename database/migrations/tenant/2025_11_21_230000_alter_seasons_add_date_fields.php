<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seasons', function (Blueprint $table) {
            if (! Schema::hasColumn('seasons', 'start_date')) {
                $table->date('start_date')->nullable()->after('name');
            }
            if (! Schema::hasColumn('seasons', 'end_date')) {
                $table->date('end_date')->nullable()->after('start_date');
            }
            if (! Schema::hasColumn('seasons', 'is_fixed_range')) {
                $table->boolean('is_fixed_range')->default(false)->after('end_date');
            }
            if (! Schema::hasColumn('seasons', 'check_in_days')) {
                $table->json('check_in_days')->nullable()->after('min_stay');
            }
        });
    }

    public function down(): void
    {
        Schema::table('seasons', function (Blueprint $table) {
            if (Schema::hasColumn('seasons', 'check_in_days')) {
                $table->dropColumn('check_in_days');
            }
            if (Schema::hasColumn('seasons', 'is_fixed_range')) {
                $table->dropColumn('is_fixed_range');
            }
            if (Schema::hasColumn('seasons', 'end_date')) {
                $table->dropColumn('end_date');
            }
            if (Schema::hasColumn('seasons', 'start_date')) {
                $table->dropColumn('start_date');
            }
        });
    }
};
