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
            $table->foreignId('property_id')->nullable()->after('id');
        });

        // Assign existing services to the first property
        $firstPropertyId = \Illuminate\Support\Facades\DB::table('properties')->value('id');
        if ($firstPropertyId) {
            \Illuminate\Support\Facades\DB::table('services')->update(['property_id' => $firstPropertyId]);
        } else {
            // If no properties exist, we can't assign a property_id.
            // In this case, we might want to truncate services or just leave them null (but we want non-null).
            // For now, let's assume properties exist or we truncate.
            if (\Illuminate\Support\Facades\DB::table('services')->count() > 0) {
                \Illuminate\Support\Facades\DB::table('services')->truncate();
            }
        }

        Schema::table('services', function (Blueprint $table) {
            $table->unsignedBigInteger('property_id')->nullable(false)->change();
            $table->foreign('property_id')->references('id')->on('properties')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropForeign(['property_id']);
            $table->dropColumn('property_id');
        });
    }
};
