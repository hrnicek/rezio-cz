<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignUuid('folio_id')->constrained('folios')->cascadeOnDelete();
            $table->string('type')->index();
            $table->string('name');
            $table->integer('quantity')->default(1);
            $table->bigInteger('unit_price_amount')->unsigned()->default(0);
            $table->bigInteger('total_price_amount')->unsigned()->default(0);
            $table->bigInteger('net_amount')->unsigned()->default(0);
            $table->bigInteger('tax_amount')->unsigned()->default(0);
            $table->integer('tax_rate')->unsigned()->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_items');
    }
};