<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PropertyTest extends TestCase
{
    use RefreshDatabase;

    public function test_properties_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/properties');

        $response->assertStatus(200);
    }

    public function test_property_can_be_created(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/properties', [
            'name' => 'Test Property',
            'address' => '123 Test St',
            'description' => 'A lovely test property',
        ]);

        $response->assertRedirect('/properties');
        $this->assertDatabaseHas('properties', [
            'name' => 'Test Property',
            'user_id' => $user->id,
        ]);
    }

    public function test_property_can_be_updated(): void
    {
        $user = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->put("/properties/{$property->id}", [
            'name' => 'Updated Property',
            'address' => '456 Updated St',
            'description' => 'Updated description',
        ]);

        $response->assertRedirect('/properties');
        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
            'name' => 'Updated Property',
        ]);
    }

    public function test_property_can_be_deleted(): void
    {
        $user = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->delete("/properties/{$property->id}");

        $response->assertRedirect('/properties');
        $this->assertDatabaseMissing('properties', [
            'id' => $property->id,
        ]);
    }

    public function test_user_cannot_manage_others_properties(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $user1->id]);

        $response = $this->actingAs($user2)->put("/properties/{$property->id}", [
            'name' => 'Hacked Property',
        ]);

        $response->assertStatus(403);
    }
}
