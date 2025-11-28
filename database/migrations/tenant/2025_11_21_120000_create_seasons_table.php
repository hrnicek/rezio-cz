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
            $table->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name'); // LÉTO / ZIMA / MIMOSEZONA / SILVESTR
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_fixed_range')->default(false);
            $table->integer('priority')->default(0);
            $table->boolean('is_recurring')->default(false);
            $table->string('start_month_day')->nullable(); // MM-DD format
            $table->string('end_month_day')->nullable(); // MM-DD format
            $table->integer('min_stay')->default(1);
            $table->json('check_in_days')->nullable();
            $table->boolean('is_default')->default(false);
            $table->decimal('price', 10, 2)->default(0);
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
