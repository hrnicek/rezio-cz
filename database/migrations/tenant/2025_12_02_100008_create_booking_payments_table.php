<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('booking_id')->constrained('bookings');
            $table->foreignUuid('folio_id')->constrained('folios');
            $table->bigInteger('amount')->unsigned();
            $table->string('currency', 3)->default('CZK');
            $table->string('status')->default('pending')->index();
            $table->text('notes')->nullable();
            $table->string('payment_method');
            $table->string('gateway')->nullable();
            $table->string('transaction_reference')->nullable()->index();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_payments');
    }
};