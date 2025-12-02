<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('property_id')->nullable();
            $table->string('type')->index();
            $table->string('subject');
            $table->string('title');
            $table->string('trigger_reference');
            $table->integer('trigger_offset_days')->default(0);
            $table->text('content');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['property_id', 'type']);
            $table->foreign('property_id')->references('id')->on('properties')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
