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

        $response = $this->actingAs($user)->get(route('admin.properties.index'));

        $response->assertStatus(200);
    }

    public function test_property_can_be_created(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from(route('admin.properties.index'))->post(route('admin.properties.store'), [
            'name' => 'Test Property',
            'address' => '123 Test St',
            'description' => 'A lovely test property',
        ]);

        $response->assertRedirect(route('admin.properties.index'));
        $this->assertDatabaseHas('properties', [
            'name' => 'Test Property',
            'user_id' => $user->id,
        ]);
    }

    public function test_property_can_be_updated(): void
    {
        $user = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->from(route('admin.properties.index'))->put(route('admin.properties.update', $property->id), [
            'name' => 'Updated Property',
            'address' => '456 Updated St',
            'description' => 'Updated description',
        ]);

        $response->assertRedirect(route('admin.properties.index'));
        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
            'name' => 'Updated Property',
        ]);
    }

    public function test_property_can_be_deleted(): void
    {
        $user = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->from(route('admin.properties.index'))->delete(route('admin.properties.destroy', $property->id));

        $response->assertRedirect(route('admin.properties.index'));
        $this->assertDatabaseMissing('properties', [
            'id' => $property->id,
        ]);
    }

    public function test_user_can_update_other_users_properties_temporarily(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $user1->id]);

        $response = $this->actingAs($user2)
            ->from(route('admin.properties.index'))
            ->put(route('admin.properties.update', $property->id), [
                'name' => 'Updated By Other User',
            ]);

        $response->assertRedirect(route('admin.properties.index'));
        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
            'name' => 'Updated By Other User',
        ]);
    }
}
