<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('folios', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignUuid('customer_id')->nullable()->constrained('customers');
            $table->string('name')->default('Hlavní účet');
            $table->string('status')->default('open')->index();
            $table->bigInteger('total_amount')->default(0);
            $table->string('currency', 3)->default('CZK');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('folios');
    }
};