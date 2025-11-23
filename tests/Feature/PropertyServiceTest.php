<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertyServiceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_list_services_for_a_property()
    {
        $user = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $user->id]);
        $service = Service::create([
            'property_id' => $property->id,
            'name' => 'Breakfast',
            'price' => 100,
            'price_type' => 'per_day',
        ]);

        $response = $this->actingAs($user)->get(route('admin.properties.services.index', $property));

        $response->assertStatus(200)
            ->assertInertia(
                fn($page) => $page
                    ->component('Admin/Properties/Services/Index')
                    ->has('services', 1)
                    ->where('services.0.name', 'Breakfast')
            );
    }

    /** @test */
    public function it_can_create_a_service()
    {
        $user = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post(route('admin.properties.services.store', $property), [
            'name' => 'Parking',
            'price' => 200,
            'price_type' => 'flat',
            'max_quantity' => 1,
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.properties.services.index', $property));
        $this->assertDatabaseHas('services', [
            'property_id' => $property->id,
            'name' => 'Parking',
            'price' => 200,
        ]);
    }

    /** @test */
    public function it_can_update_a_service()
    {
        $user = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $user->id]);
        $service = Service::create([
            'property_id' => $property->id,
            'name' => 'Old Name',
            'price' => 100,
            'price_type' => 'per_day',
        ]);

        $response = $this->actingAs($user)->put(route('admin.properties.services.update', [$property, $service]), [
            'name' => 'New Name',
            'price' => 150,
            'price_type' => 'per_day',
            'max_quantity' => 0,
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.properties.services.index', $property));
        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'name' => 'New Name',
            'price' => 150,
        ]);
    }

    /** @test */
    public function it_can_delete_a_service()
    {
        $user = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $user->id]);
        $service = Service::create([
            'property_id' => $property->id,
            'name' => 'To Delete',
            'price' => 100,
            'price_type' => 'per_day',
        ]);

        $response = $this->actingAs($user)->delete(route('admin.properties.services.destroy', [$property, $service]));

        $response->assertRedirect(route('admin.properties.services.index', $property));
        $this->assertSoftDeleted('services', ['id' => $service->id]);
    }
}
