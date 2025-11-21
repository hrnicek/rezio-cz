<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('code')->unique()->nullable()->after('id');
            $table->foreignId('customer_id')->nullable()->after('user_id')->constrained('customers')->nullOnDelete();
            $table->dateTime('date_start')->nullable()->after('end_date');
            $table->dateTime('date_end')->nullable()->after('date_start');

            // Make user_id nullable if it's not already
            $table->foreignId('user_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['code', 'customer_id', 'date_start', 'date_end']);
            // We can't easily revert user_id to not null without knowing if there are nulls
        });
    }
};
