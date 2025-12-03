<?php

namespace Tests\Feature;

use App\Models\Central\Tenant;
use App\Models\Property;
use App\Models\CRM\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Carbon;

class WidgetCompanyBillingTest extends TestCase
{
    protected $tenant;
    protected $property;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a tenant for testing
        $this->tenant = Tenant::query()->create([
            'id' => 'test_widget_'.uniqid(),
        ]);
        
        $this->tenant->domains()->create([
            'domain' => $this->tenant->id,
        ]);

        // Initialize tenancy
        tenancy()->initialize($this->tenant);

        // Create property
        $this->property = Property::query()->create([
            'name' => 'Test Property',
            'slug' => 'test-property',
        ]);
    }

    protected function tearDown(): void
    {
        if ($this->tenant) {
            $this->tenant->delete();
        }
        parent::tearDown();
    }

    public function test_booking_creation_saves_company_billing_fields()
    {
        $start = \Illuminate\Support\Facades\Date::now()->addDays(10)->format('Y-m-d');
        $end = \Illuminate\Support\Facades\Date::now()->addDays(12)->format('Y-m-d');

        $payload = [
            'start_date' => $start,
            'end_date' => $end,
            'customer' => [
                'first_name' => 'Jan',
                'last_name' => 'Novak',
                'email' => 'jan.novak@company.com',
                'phone' => '+420123456789',
                'note' => 'Test note',
                'is_company' => true,
                'company_name' => 'My Company Ltd.',
                'ico' => '12345678',
                'dic' => 'CZ12345678',
                'has_vat' => true,
                'billing_street' => 'Business St. 1',
                'billing_city' => 'Prague',
                'billing_zip' => '11000',
                'billing_country' => 'CZ',
            ],
            'addons' => [],
        ];

        $response = $this->postJson(
            "http://{$this->tenant->id}.rezio.test/api/widgets/{$this->property->id}/reservations", 
            $payload
        );

        $response->assertStatus(201);

        $this->assertDatabaseHas('customers', [
            'email' => 'jan.novak@company.com',
            'is_company' => true,
            'company_name' => 'My Company Ltd.',
            'ico' => '12345678',
            'dic' => 'CZ12345678',
            'has_vat' => true,
            'billing_street' => 'Business St. 1',
            'billing_city' => 'Prague',
            'billing_zip' => '11000',
            'billing_country' => 'CZ',
        ]);
    }

    public function test_booking_creation_without_company_fields()
    {
        $start = \Illuminate\Support\Facades\Date::now()->addDays(15)->format('Y-m-d');
        $end = \Illuminate\Support\Facades\Date::now()->addDays(17)->format('Y-m-d');

        $payload = [
            'start_date' => $start,
            'end_date' => $end,
            'customer' => [
                'first_name' => 'Petr',
                'last_name' => 'Svoboda',
                'email' => 'petr@example.com',
                'phone' => '+420987654321',
                'is_company' => false,
            ],
            'addons' => [],
        ];

        $response = $this->postJson(
            "http://{$this->tenant->id}.rezio.test/api/widgets/{$this->property->id}/reservations", 
            $payload
        );

        $response->assertStatus(201);

        $this->assertDatabaseHas('customers', [
            'email' => 'petr@example.com',
            'is_company' => false,
            'company_name' => null,
        ]);
    }
}
