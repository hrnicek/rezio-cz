<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type')->default('invoice');
            $table->foreignUuid('booking_id')->constrained('bookings');
            $table->foreignUuid('folio_id')->nullable()->constrained('folios');
            $table->foreignUuid('payment_id')->nullable()->constrained('booking_payments')->nullOnDelete();
            $table->string('number')->unique();
            $table->string('variable_symbol')->nullable();
            $table->date('issued_date');
            $table->date('due_date');
            $table->date('tax_date');
            $table->string('supplier_name');
            $table->string('supplier_ico')->nullable();
            $table->string('supplier_dic')->nullable();
            $table->string('supplier_address');
            $table->string('customer_name');
            $table->string('customer_ico')->nullable();
            $table->string('customer_dic')->nullable();
            $table->string('customer_address');
            $table->bigInteger('total_price_amount')->unsigned();
            $table->bigInteger('total_net_amount')->unsigned();
            $table->bigInteger('total_tax_amount')->unsigned();
            $table->string('currency', 3)->default('CZK');
            $table->string('status')->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
