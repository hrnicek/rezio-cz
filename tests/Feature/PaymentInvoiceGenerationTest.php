<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Enums\PaymentMethod;
use App\Models\Booking\Booking;
use App\Models\Booking\Folio;
use App\Models\Finance\Invoice;
use App\Models\Finance\Payment;
use App\Models\Central\Tenant;
use App\States\Payment\Paid;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PaymentInvoiceGenerationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure we are in a tenant context if required
        // For simplicity, if the app is multi-tenant, we might need to setup a tenant
        // But if I run this test, I need to know if it runs against central or tenant DB.
        // Assuming standard setup where tests might run with tenancy disabled or I need to enable it.
        
        // Let's try to create a tenant and initialize it
        $tenant = Tenant::create(['id' => 'test-tenant']);
        tenancy()->initialize($tenant);
        
        // Run migrations for tenant
        // This might be slow or problematic if not configured for testing
        // But let's try standard approach
    }

    public function test_invoice_is_created_when_payment_is_created_as_paid()
    {
        $booking = Booking::factory()->create();
        $folio = Folio::factory()->create(['booking_id' => $booking->id]);

        $payment = Payment::create([
            'booking_id' => $booking->id,
            'folio_id' => $folio->id,
            'amount' => 1000,
            'currency' => 'CZK',
            'status' => Paid::class, // Or 'paid' if cast handles it
            'payment_method' => 'card', // Assuming string or enum
            'paid_at' => now(),
        ]);

        $this->assertDatabaseHas('invoices', [
            'booking_id' => $booking->id,
            'type' => InvoiceType::PaymentConfirmation->value,
            'total_price_amount' => 1000,
            'status' => InvoiceStatus::Paid->value,
        ]);
    }
}
