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
        Schema::table('services', function (Blueprint $table) {
            // Modify enum to include all supported types
            $table->enum('price_type', [
                'per_person',
                'per_night',
                'per_day',
                'per_stay',
                'fixed',
                'flat',
                'per_hour',
            ])->default('fixed')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            // Revert to original limited set (warning: data loss if other values used)
            $table->enum('price_type', ['per_day', 'flat', 'per_stay'])->default('flat')->change();
        });
    }
};
