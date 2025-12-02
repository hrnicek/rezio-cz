<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->nullable();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('customer_id')->constrained()->cascadeOnDelete();
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->time('arrival_time_estimate')->nullable();
            $table->time('departure_time_estimate')->nullable();
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamp('checked_out_at')->nullable();
            $table->string('status')->default('pending')->index();
            $table->uuid('token')->nullable()->unique();
            $table->bigInteger('total_price_amount')->unsigned()->default(0);
            $table->string('currency', 3)->default('CZK');
            $table->text('notes')->nullable();
            $table->timestamp('reminders_sent_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['property_id', 'status', 'check_in_date', 'check_out_date'], 'bookings_avail_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
