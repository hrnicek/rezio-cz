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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete()->nullable();
            $table->foreignId('season_id')->nullable()->constrained('seasons')->nullOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->dateTime('date_start')->nullable();
            $table->dateTime('date_end')->nullable();
            $table->string('status')->default('pending'); // pending, confirmed, paid, cancelled
            $table->uuid('checkin_token')->nullable()->unique();
            $table->decimal('total_price', 10, 2);
            $table->string('currency', 3)->default('CZK');
            $table->decimal('exchange_rate', 10, 4)->default(1.0000);
            $table->text('notes')->nullable();
            $table->timestamp('reminders_sent_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Performance indexes
            $table->index(['property_id', 'status', 'start_date', 'end_date'], 'bookings_availability_idx');
            $table->index(['property_id', 'status', 'date_start', 'date_end'], 'bookings_overlap_idx');
            $table->index('checkin_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
