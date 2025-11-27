<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (! Schema::hasColumn('customers', 'uuid')) {
                $table->uuid('uuid')->nullable()->after('id');
            } else {
                $table->uuid('uuid')->nullable()->change();
            }
        });

        // Backfill UUIDs
        DB::table('customers')
            ->whereNull('uuid')
            ->orWhere('uuid', '')
            ->orderBy('id')
            ->chunk(100, function ($customers) {
                foreach ($customers as $customer) {
                    DB::table('customers')
                        ->where('id', $customer->id)
                        ->update(['uuid' => (string) \Illuminate\Support\Str::uuid()]);
                }
            });

        Schema::table('customers', function (Blueprint $table) {
            $table->string('uuid')->nullable(false)->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'uuid')) {
                $table->dropColumn('uuid');
            }
        });
    }
};
