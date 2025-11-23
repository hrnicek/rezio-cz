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
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->dateTime('date_start')->nullable();
            $table->dateTime('date_end')->nullable();
            $table->string('status')->default('pending'); // pending, confirmed, paid, cancelled
            $table->decimal('total_price', 10, 2);
            $table->text('notes')->nullable();
            $table->timestamp('reminders_sent_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
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
