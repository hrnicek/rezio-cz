<?php

namespace Tests\Feature\Widget;

use App\Models\Central\Tenant;
use App\Models\Configuration\Season;
use App\Models\Property;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class WidgetBookingTest extends TestCase
{
    protected $tenant;
    protected $property;
    protected $defaultSeason;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a tenant for testing
        $this->tenant = Tenant::create([
            'id' => 'test_widget_booking_' . uniqid(),
        ]);

        $this->tenant->domains()->create([
            'domain' => $this->tenant->id,
        ]);

        // Initialize tenancy
        tenancy()->initialize($this->tenant);

        // Create property with UUID
        $this->property = Property::create([
            'name' => 'Test Property',
            'slug' => 'test-property',
        ]);

        // Create default season for pricing
        $this->defaultSeason = Season::create([
            'property_id' => $this->property->id,
            'name' => 'Default Season',
            'is_default' => true,
            'price_amount' => 200000, // 2000 CZK per night in cents
        ]);
    }

    protected function tearDown(): void
    {
        if ($this->tenant) {
            $this->tenant->delete();
        }
        parent::tearDown();
    }

    public function test_widget_booking_with_uuid_property_succeeds(): void
    {
        $start = \Illuminate\Support\Facades\Date::now()->addDays(10)->format('Y-m-d');
        $end = \Illuminate\Support\Facades\Date::now()->addDays(12)->format('Y-m-d');

        $payload = [
            'start_date' => $start,
            'end_date' => $end,
            'customer' => [
                'first_name' => 'Jakub',
                'last_name' => 'Novák',
                'email' => 'jakub@test.com',
                'phone' => '+420731786686',
                'note' => 'Test booking',
                'is_company' => false,
            ],
            'addons' => [],
            'guests_count' => 2,
        ];

        $response = $this->postJson(
            "http://{$this->tenant->id}.rezio.test/api/widgets/{$this->property->id}/reservations",
            $payload
        );

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'success',
            'booking_id',
            'booking_code',
            'total_price',
            'currency',
        ]);
        $response->assertJson(['success' => true]);

        // Verify booking was created
        $this->assertDatabaseHas('bookings', [
            'property_id' => $this->property->id,
            'guests_count' => 2,
        ]);

        // Verify customer was created
        $this->assertDatabaseHas('customers', [
            'email' => 'jakub@test.com',
            'first_name' => 'Jakub',
            'last_name' => 'Novák',
        ]);
    }

    public function test_widget_booking_validation_error_returns_proper_message(): void
    {
        // Create a season with min_persons requirement
        Season::create([
            'property_id' => $this->property->id,
            'name' => 'High Season',
            'is_default' => false,
            'priority' => 10,
            'start_date' => \Illuminate\Support\Facades\Date::now()->addDays(5),
            'end_date' => \Illuminate\Support\Facades\Date::now()->addDays(15),
            'price_amount' => 300000,
            'min_persons' => 4, // Require minimum 4 persons
        ]);

        $start = \Illuminate\Support\Facades\Date::now()->addDays(10)->format('Y-m-d');
        $end = \Illuminate\Support\Facades\Date::now()->addDays(12)->format('Y-m-d');

        $payload = [
            'start_date' => $start,
            'end_date' => $end,
            'customer' => [
                'first_name' => 'Jakub',
                'last_name' => 'Novák',
                'email' => 'jakub@test.com',
                'phone' => '+420731786686',
                'is_company' => false,
            ],
            'addons' => [],
            'guests_count' => 2, // Less than required minimum
        ];

        $response = $this->postJson(
            "http://{$this->tenant->id}.rezio.test/api/widgets/{$this->property->id}/reservations",
            $payload
        );

        // Should return 422 with specific validation message
        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
        
        // Should NOT return generic error message
        $this->assertNotEquals(
            'An unexpected error occurred. Please try again later.',
            $response->json('message')
        );
        
        // Should mention the business rule violation
        $this->assertStringContainsString(
            'Business rule validation failed',
            $response->json('message')
        );
    }

    public function test_widget_booking_with_invalid_dates_returns_validation_error(): void
    {
        $start = \Illuminate\Support\Facades\Date::now()->addDays(12)->format('Y-m-d');
        $end = \Illuminate\Support\Facades\Date::now()->addDays(10)->format('Y-m-d'); // End before start

        $payload = [
            'start_date' => $start,
            'end_date' => $end,
            'customer' => [
                'first_name' => 'Test',
                'last_name' => 'User',
                'email' => 'test@test.com',
                'phone' => '+420123456789',
                'is_company' => false,
            ],
            'addons' => [],
        ];

        $response = $this->postJson(
            "http://{$this->tenant->id}.rezio.test/api/widgets/{$this->property->id}/reservations",
            $payload
        );

        // Laravel validation should catch this before it reaches the service
        $response->assertStatus(422);
    }
}
