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
        Schema::create('billing_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();

            // A) Identity & Tax (Fakturační údaje)
            $table->boolean('is_vat_payer')->default(false);
            $table->string('ico')->nullable();
            $table->string('dic')->nullable();
            $table->string('company_name')->nullable(); // Billing Name
            $table->string('street')->nullable();
            $table->string('city')->nullable();
            $table->string('zip')->nullable();
            $table->char('country', 2)->default('CZ');
            $table->text('default_note')->nullable();

            // B) Banking (Bankovní účet)
            $table->string('bank_account')->nullable(); // Format: 000000/0000
            $table->string('iban')->nullable();
            $table->string('swift')->nullable();
            $table->string('currency', 3)->default('CZK');
            $table->boolean('show_bank_account')->default(true);

            // C) Document Numbering (Číslování dokladů)
            // Proforma
            $table->string('proforma_prefix')->default('ZF');
            $table->unsignedInteger('proforma_current_sequence')->default(1);
            // Invoice
            $table->string('invoice_prefix')->default('FA');
            $table->unsignedInteger('invoice_current_sequence')->default(1);
            // Receipt
            $table->string('receipt_prefix')->default('DP');
            $table->unsignedInteger('receipt_current_sequence')->default(1);

            // D) Logic
            $table->integer('due_days')->default(14); // Invoice due date offset

            $table->timestamps();

            $table->unique('property_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_settings');
    }
};
