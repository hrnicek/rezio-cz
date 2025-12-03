<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('property_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_vat_payer')->default(false);
            $table->string('ico')->nullable();
            $table->string('dic')->nullable();
            $table->string('company_name')->nullable();
            $table->string('street')->nullable();
            $table->string('city')->nullable();
            $table->string('zip')->nullable();
            $table->char('country', 2)->default('CZ');
            $table->text('default_note')->nullable();
            $table->string('bank_account')->nullable();
            $table->string('iban')->nullable();
            $table->string('swift')->nullable();
            $table->string('currency', 3)->default('CZK');
            $table->boolean('show_bank_account')->default(true);
            $table->string('proforma_prefix')->default('ZF');
            $table->unsignedInteger('proforma_current_sequence')->default(1);
            $table->string('invoice_prefix')->default('FA');
            $table->unsignedInteger('invoice_current_sequence')->default(1);
            $table->string('receipt_prefix')->default('DP');
            $table->unsignedInteger('receipt_current_sequence')->default(1);
            $table->integer('due_days')->default(14);
            $table->timestamps();
            $table->unique('property_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_settings');
    }
};
