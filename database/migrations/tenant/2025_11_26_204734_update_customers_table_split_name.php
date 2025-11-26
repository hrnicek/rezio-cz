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
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'first_name')) {
                $table->string('first_name')->nullable()->after('id');
            }
            if (!Schema::hasColumn('customers', 'last_name')) {
                $table->string('last_name')->nullable()->after('first_name');
            }
            if (Schema::hasColumn('customers', 'name')) {
                $table->dropColumn('name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('name')->nullable()->after('id');
            $table->dropColumn(['first_name', 'last_name']);
        });
    }
};
