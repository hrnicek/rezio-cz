<?php

namespace Tests\Feature\Api;

use App\Models\Booking;
use App\Models\Service;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TenantTestCase;

class ServiceApiTest extends TenantTestCase
{

    public function test_lists_active_services()
    {
        $property = \App\Models\Property::factory()->create();
        Service::create(['property_id' => $property->id, 'name' => 'Pes', 'is_active' => true, 'price' => 100, 'price_type' => 'per_stay', 'max_quantity' => 5]);
        Service::create(['property_id' => $property->id, 'name' => 'Postýlka', 'is_active' => false, 'price' => 50, 'price_type' => 'per_stay', 'max_quantity' => 2]);

        $res = $this->getJson(route('api.services.index'));
        $res->assertSuccessful();

        $ids = collect($res->json('services'))->pluck('name');

        $this->assertContains('Pes', $ids);
        $this->assertNotContains('Postýlka', $ids);
    }

    public function test_checks_availability_and_respects_max_quantity()
    {
        $property = \App\Models\Property::factory()->create();
        $service = Service::create([
            'property_id' => $property->id,
            'name' => 'Postýlka',
            'price_type' => 'per_stay',
            'price' => 300,
            'max_quantity' => 2,
            'is_active' => true,
        ]);

        $customer = Customer::create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'phone' => '123456789',
        ]);

        // Create a booking that uses 1 quantity
        // Create a booking that uses 1 quantity
        $booking = Booking::factory()->create([
            'code' => '123456',
            'customer_id' => $customer->id,
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-05',
            'date_start' => '2024-01-01 14:00:00',
            'date_end' => '2024-01-05 10:00:00',
            'total_price' => 5000,
            'status' => 'confirmed',
        ]);

        $booking->services()->attach($service->id, [
            'quantity' => 1,
            'price_total' => 300
        ]);

        // Check availability for same dates
        $res = $this->postJson(route('api.services.availability'), [
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-05',
            'selections' => [
                ['service_id' => $service->id, 'quantity' => 2] // Requesting 2, but 1 is taken. Max is 2. So 1+2=3 > 2. Should fail.
            ]
        ]);

        $res->assertSuccessful();
        $data = $res->json();

        $this->assertFalse($data['available']);
        $this->assertFalse($data['items'][0]['is_available']);
        $this->assertEquals($service->id, $data['items'][0]['service_id']);
    }
}
