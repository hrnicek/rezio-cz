<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seasonal_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->string('name');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('price', 10, 2);
            $table->unsignedInteger('priority')->default(1);
            $table->timestamps();

            $table->index(['property_id', 'start_date', 'end_date']);
            $table->index(['property_id', 'priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seasonal_prices');
    }
};

