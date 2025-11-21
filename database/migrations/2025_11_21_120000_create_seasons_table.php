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
        Schema::create('seasons', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // LÉTO / ZIMA / MIMOSEZONA / SILVESTR
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_fixed_range')->default(false);
            $table->boolean('is_default')->default(false);
            $table->integer('min_stay')->default(1);
            $table->json('check_in_days')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seasons');
    }
};
