<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ==========================================
        // 1. ADMIN & AUTH (BigInt - Interní svět)
        // ==========================================

        // Users: Admini a personál
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // BigInt
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->string('profile_photo_path', 2048)->nullable();
            
            // Interní vazba na poslední spravovanou property
            $table->foreignId('current_property_id')->nullable(); 
            
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // ==========================================
        // 2. CRM & PROPERTY (Hybridní)
        // ==========================================

        // Properties: Nemovitosti (BigInt - interní číselník)
        Schema::create('properties', function (Blueprint $table) {
            $table->id(); // BigInt
            $table->string('name');
            $table->string('slug')->unique(); // Veřejný identifikátor v URL
            
            // Adresa (pro jednoduchost text, nebo rozbít na sloupce)
            $table->text('address')->nullable(); 
            $table->text('description')->nullable();

            $table->time('default_check_in_time')->default('15:00:00');
            $table->time('default_check_out_time')->default('10:00:00');

            $table->string('image')->nullable();
            
            $table->timestamps();
        });

        // Customers: Plátci / Firmy (UUID - Bezpečné ID pro faktury)
        Schema::create('customers', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUID
            
            $table->string('email')->index();
            $table->string('phone')->nullable();
            $table->boolean('is_company')->default(false);
            
            // Fakturační identita
            $table->string('first_name');
            $table->string('last_name');
            $table->string('company_name')->nullable(); // Pro firmy povinné (validace v PHP)
            $table->string('ico')->nullable();
            $table->string('dic')->nullable();
            $table->boolean('has_vat')->default(false);
            
            // Fakturační adresa
            $table->string('billing_street')->nullable();
            $table->string('billing_city')->nullable();
            $table->string('billing_zip')->nullable();
            $table->char('billing_country', 2)->default('CZ');
            
            $table->text('internal_notes')->nullable();
            $table->boolean('is_registered')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        // ==========================================
        // 3. BOOKING ENGINE (UUID - Veřejný svět)
        // ==========================================

        // Bookings: Hlavní kontejner rezervace
        Schema::create('bookings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->nullable(); // Čitelný kód (např. 2025-ABC)

            // HYBRIDNÍ VAZBY
            $table->foreignId('property_id')->constrained()->cascadeOnDelete(); // Vazba na BigInt
            $table->foreignUuid('customer_id')->constrained()->cascadeOnDelete(); // Vazba na UUID (Main Booker)
            
            // Logika času
            $table->date('check_in_date');
            $table->date('check_out_date');

            // Logistika (Nullable - pokud je null, platí default z Property)
            $table->time('arrival_time_estimate')->nullable(); // Např. "18:30:00" (Host nahlásil zpoždění)
            $table->time('departure_time_estimate')->nullable(); // Např. "12:00:00" (Zaplatil late check-out)
    
            // Audit (Kdy skutečně dostal klíče - Timestamp)
            $table->timestamp('checked_in_at')->nullable(); 
            $table->timestamp('checked_out_at')->nullable();
            
            $table->string('status')->default('pending')->index();
            $table->uuid('token')->nullable()->unique(); // Public access token
            
            // Finance Cache (Součet všech folií)
            $table->bigInteger('total_price_amount')->unsigned()->default(0);
            $table->string('currency', 3)->default('CZK');
            
            $table->text('notes')->nullable();
            $table->timestamp('reminders_sent_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Index pro rychlé hledání v kalendáři
            $table->index(['property_id', 'status', 'check_in_date', 'check_out_date'], 'bookings_avail_idx');
        });

        // Folios: Virtuální účty uvnitř rezervace
        Schema::create('folios', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('booking_id')->constrained('bookings')->cascadeOnDelete();
            
            // Kdo platí tento konkrétní pod-účet? (nullable = platí hlavní customer)
            $table->foreignUuid('customer_id')->nullable()->constrained('customers');
            
            $table->string('name')->default('Hlavní účet'); // "Main Folio", "Room Service"
            $table->string('status')->default('open')->index(); // open, closed, invoiced
            
            $table->bigInteger('total_amount')->default(0);
            $table->string('currency', 3)->default('CZK');
            
            $table->timestamps();
            $table->softDeletes();
        });

        // Booking Items: Konkrétní položky na účtu
        Schema::create('booking_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            $table->foreignUuid('booking_id')->constrained('bookings')->cascadeOnDelete(); // Pro rychlý fetch
            $table->foreignUuid('folio_id')->constrained('folios')->cascadeOnDelete(); // Pro logiku plateb
            
            $table->string('type')->index(); // Enum: night, service, fee
            $table->string('name');
            $table->integer('quantity')->default(1);
            
            // CENOVÝ SNAPSHOT (Fixované ceny v momentě přidání)
            $table->bigInteger('unit_price_amount')->unsigned()->default(0);
            $table->bigInteger('total_price_amount')->unsigned()->default(0);
            $table->bigInteger('net_amount')->unsigned()->default(0);
            $table->bigInteger('tax_amount')->unsigned()->default(0);
            $table->integer('tax_rate')->unsigned()->default(0); // 2100 = 21%
            
            $table->timestamps();
        });

        // Payments: Transakce
        Schema::create('booking_payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Platba se váže k Foliu (úhradě dluhu)
            $table->foreignUuid('booking_id')->constrained('bookings');
            $table->foreignUuid('folio_id')->constrained('folios');
            
            $table->bigInteger('amount')->unsigned();
            $table->string('currency', 3)->default('CZK');
            
            $table->string('status')->default('pending')->index(); // pending, paid, failed
            $table->string('payment_method'); // card, bank, cash
            $table->string('gateway')->nullable(); // stripe, gopay
            // ZMĚNA: transaction_id -> transaction_reference
            $table->string('transaction_reference')->nullable()->index();            
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        // ==========================================
        // 4. ACCOUNTING (Invoicing)
        // ==========================================

        // Invoices: Daňové doklady (SNAPSHOTS)
        Schema::create('invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            $table->foreignUuid('booking_id')->constrained('bookings');
            $table->foreignUuid('folio_id')->nullable()->constrained('folios'); // Zdroj dat
            
            $table->string('number')->unique(); // VS: 20250001
            $table->string('variable_symbol')->nullable();
            
            $table->date('issued_date');
            $table->date('due_date');
            $table->date('tax_date'); // DUZP
            
            // SNAPSHOT: Dodavatel (Property v čase vystavení)
            $table->string('supplier_name');
            $table->string('supplier_ico')->nullable();
            $table->string('supplier_dic')->nullable();
            $table->string('supplier_address');
            
            // SNAPSHOT: Odběratel (Customer v čase vystavení)
            $table->string('customer_name');
            $table->string('customer_ico')->nullable();
            $table->string('customer_dic')->nullable();
            $table->string('customer_address');
            
            // Součty
            $table->bigInteger('total_price_amount')->unsigned();
            $table->bigInteger('total_net_amount')->unsigned();
            $table->bigInteger('total_tax_amount')->unsigned();
            $table->string('currency', 3)->default('CZK');
            
            $table->string('status')->default('draft'); // draft, issued, paid, cancelled
            $table->text('notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });

        // Invoice Items: Položky na faktuře (Neměnné)
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('invoice_id')->constrained('invoices')->cascadeOnDelete();
            
            $table->string('name');
            $table->integer('quantity');
            
            $table->bigInteger('unit_price_amount');
            $table->bigInteger('total_price_amount');
            $table->bigInteger('tax_amount');
            $table->integer('tax_rate');
            
            $table->timestamps();
        });

        // ==========================================
        // 5. INVENTORY & SETTINGS (BigInt)
        // ==========================================

        // Services: Ceník služeb
        Schema::create('services', function (Blueprint $table) {
            $table->id(); // BigInt
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('price_type', ['per_person', 'per_night', 'per_day', 'per_stay', 'fixed', 'flat', 'per_hour'])->default('fixed');
            $table->bigInteger('price_amount')->unsigned()->default(0);
            $table->unsignedInteger('max_quantity')->default(0);
            $table->boolean('is_active')->default(true);
            
            $table->softDeletes();
            $table->timestamps();
        });

        // Seasons: Ceník ubytování
        Schema::create('seasons', function (Blueprint $table) {
            $table->id(); // BigInt
            $table->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
            
            $table->string('name');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            
            $table->boolean('is_fixed_range')->default(false);
            $table->integer('priority')->default(0); // Vyšší vyhrává
            
            $table->integer('min_stay')->default(1);
            $table->json('check_in_days')->nullable();
            $table->boolean('is_default')->default(false);
            
            $table->bigInteger('price_amount')->unsigned()->default(0);
            $table->timestamps();
        });

        // Blackout Dates: Blokace
        Schema::create('blackout_dates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('reason')->nullable();
            $table->timestamps();
        });

        // Guests: Fyzické osoby na pokoji
        Schema::create('guests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('booking_id')->constrained('bookings')->cascadeOnDelete();
            
            $table->string('first_name');
            $table->string('last_name');
            $table->boolean('is_adult')->default(true);
            
            $table->string('gender')->nullable();
            $table->string('nationality')->nullable();
            $table->string('document_type')->nullable(); // passport, id_card
            $table->string('document_number')->nullable();
            $table->date('birth_date')->nullable();
            $table->json('address')->nullable(); // Domácí adresa hosta
            $table->longText('signature')->nullable(); // GDPR podpis
            
            $table->timestamps();
        });

        // Fileponds: Uploady (vazba na User BigInt)
        Schema::create('fileponds', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->string('filepath');
            $table->string('extension', 100);
            $table->string('mimetypes', 100);
            $table->string('disk', 100);
            $table->text('upload_id')->nullable();
            
            $table->foreignId('created_by')->nullable(); // User ID
            
            $table->dateTime('expires_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        
        // Email Templates
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('type')->index();
            $table->string('subject');
            $table->text('body');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['property_id', 'type']);
        });

        // Zde můžete vložit Activity Log / Permission tabulky
        // (Ty zůstávají standardní, protože Users jsou BigInt)
    }

    public function down(): void
    {
        // Drop tables in strict reverse order to avoid FK errors
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        
        Schema::dropIfExists('booking_payments');
        Schema::dropIfExists('booking_items');
        Schema::dropIfExists('folios');
        Schema::dropIfExists('guests');
        Schema::dropIfExists('bookings');
        
        Schema::dropIfExists('fileponds');
        Schema::dropIfExists('email_templates');
        Schema::dropIfExists('blackout_dates');
        Schema::dropIfExists('seasons');
        Schema::dropIfExists('services');
        Schema::dropIfExists('properties');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};