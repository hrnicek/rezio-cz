<?php

namespace Tests\Feature\Tenant;

use App\Models\Booking;
use App\Models\Property;
use App\Models\Configuration\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TenantTestCase;

class WidgetAvailabilityTest extends TenantTestCase
{
    use RefreshDatabase;

    public function test_availability_is_cached_and_invalidated()
    {
        $property = Property::factory()->create();
        $service = Service::factory()->create([
            'property_id' => $property->id,
            'max_quantity' => 5,
            'is_active' => true,
        ]);

        $payload = [
            'start_date' => now()->addDay()->format('Y-m-d'),
            'end_date' => now()->addDays(2)->format('Y-m-d'),
            'selections' => [
                ['service_id' => $service->id, 'quantity' => 1],
            ],
        ];

        // 1. First request - Cold Cache
        DB::enableQueryLog();
        $response = $this->postJson(route('api.widgets.services.availability', $property), $payload);
        $response->assertOk();
        $this->assertTrue($response->json('available'));

        $queries = DB::getQueryLog();
        $bookingQueries = collect($queries)->filter(fn ($q) => str_contains($q['query'], 'bookings'));
        // Should query bookings
        $this->assertGreaterThan(0, $bookingQueries->count());

        // 2. Second request - Warm Cache
        DB::flushQueryLog();
        $response2 = $this->postJson(route('api.widgets.services.availability', $property), $payload);
        $response2->assertOk();

        $queries = DB::getQueryLog();
        $bookingQueries = collect($queries)->filter(fn ($q) => str_contains($q['query'], 'bookings'));
        // Should NOT query bookings
        $this->assertCount(0, $bookingQueries, 'Bookings should be served from cache');

        // 3. Invalidate Cache
        $this->travel(1)->second(); // Ensure timestamp changes

        // Create a booking that uses the service
        $booking = Booking::factory()->create([
            'property_id' => $property->id,
            'start_date' => now()->addDay()->format('Y-m-d'),
            'end_date' => now()->addDays(2)->format('Y-m-d'),
            'date_start' => now()->addDay()->format('Y-m-d 14:00:00'),
            'date_end' => now()->addDays(2)->format('Y-m-d 10:00:00'),
            'status' => 'confirmed',
        ]);

        // Attach service to booking to reduce availability
        $booking->services()->attach($service->id, ['quantity' => 1, 'price_total' => 100]);
        // Note: attaching relations might not touch the parent model automatically unless configured.
        // Booking model has $touches = ['property'], but Booking::services() is a BelongsToMany.
        // Attaching to pivot table does NOT touch the parent Booking by default unless 'touch()' is used on relation.
        // So we must manually touch the booking or property to simulate a real update.
        $booking->touch();

        $property->refresh();
        // dump('Property updated_at after touch: ' . $property->updated_at->timestamp);

        // 4. Third request - Cache Invalidated
        DB::flushQueryLog();
        $response3 = $this->postJson(route('api.widgets.services.availability', $property), $payload);
        $response3->assertOk();

        $queries = DB::getQueryLog();
        $bookingQueries = collect($queries)->filter(fn ($q) => str_contains($q['query'], 'bookings'));
        $this->assertGreaterThan(0, $bookingQueries->count(), 'Cache should be invalidated after booking update');

        // Check if availability calculation is correct (5 max - 1 booked = 4 available)
        $items = $response3->json('items');
        $this->assertEquals(4, $items[0]['available_quantity']);
    }
}
