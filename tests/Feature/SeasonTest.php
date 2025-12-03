<?php

namespace Tests\Feature;

use App\Models\Central\Tenant;
use App\Models\Configuration\Season;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeasonTest extends TestCase
{
    // use RefreshDatabase; // Tenancy handles its own DB

    protected $tenant;
    protected $user;
    protected $property;

    protected function setUp(): void
    {
        parent::setUp();

        config(['cache.default' => 'file']);

        // Create a tenant for testing
        $this->tenant = Tenant::create([
            'id' => 'test_'.uniqid(),
        ]);
        
        $this->tenant->domains()->create([
            'domain' => $this->tenant->id,
        ]);

        // Initialize tenancy
        tenancy()->initialize($this->tenant);

        // Create user
        $this->user = User::factory()->create();
        
        // Create property
        $this->property = Property::create([
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

    public function test_can_create_season_with_new_fields()
    {
        $domain = 'http://' . $this->tenant->domains->first()->domain . '.rezio.test';
        $url = route('admin.properties.seasons.store', $this->property);
        // Ensure URL uses the correct domain
        if (!str_starts_with($url, $domain)) {
             $url = $domain . route('admin.properties.seasons.store', $this->property, false);
        }

        $response = $this->actingAs($this->user)->post($url, [
            'name' => 'Summer Season',
            'price' => 100,
            'start_date' => '2025-06-01',
            'end_date' => '2025-08-31',
            'min_stay' => 3,
            'min_persons' => 2,
            'is_recurring' => true,
            'is_full_season_booking_only' => true,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('seasons', [
            'name' => 'Summer Season',
            'min_stay' => 3,
            'min_persons' => 2,
            'is_recurring' => true,
            'is_full_season_booking_only' => true,
        ]);
    }

    public function test_can_update_season_with_new_fields()
    {
        $season = Season::create([
            'property_id' => $this->property->id,
            'name' => 'Old Season',
            'start_date' => '2025-01-01',
            'end_date' => '2025-01-31',
            'price_amount' => 5000,
            'min_stay' => 1,
            'min_persons' => 1,
            'is_recurring' => false,
            'is_full_season_booking_only' => false,
        ]);

        $domain = 'http://' . $this->tenant->domains->first()->domain . '.rezio.test';
        $url = route('admin.properties.seasons.update', [$this->property, $season]);
        if (!str_starts_with($url, $domain)) {
             $url = $domain . route('admin.properties.seasons.update', [$this->property, $season], false);
        }

        $response = $this->actingAs($this->user)->put($url, [
            'name' => 'Updated Season',
            'price' => 120,
            'start_date' => '2025-01-01',
            'end_date' => '2025-01-31',
            'min_stay' => 5,
            'min_persons' => 4,
            'is_recurring' => true,
            'is_full_season_booking_only' => true,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('seasons', [
            'id' => $season->id,
            'name' => 'Updated Season',
            'min_stay' => 5,
            'min_persons' => 4,
            'is_recurring' => true,
            'is_full_season_booking_only' => true,
        ]);
    }
}
