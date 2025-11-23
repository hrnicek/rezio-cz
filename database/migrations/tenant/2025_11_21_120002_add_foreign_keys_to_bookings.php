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
            $table->foreignId('customer_id')
                ->nullable()
                ->after('user_id')
                ->constrained('customers')
                ->nullOnDelete();

            $table->foreignId('season_id')
                ->nullable()
                ->after('customer_id')
                ->constrained('seasons')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropColumn('customer_id');
            $table->dropForeign(['season_id']);
            $table->dropColumn('season_id');
        });
    }
};
