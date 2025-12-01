<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use RahulHaque\Filepond\Facades\Filepond;
use Tests\TestCase;

class PropertyImageUploadTest extends TestCase
{
    use RefreshDatabase;

    protected $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $id = 'test_'.uniqid();
        $this->tenant = \App\Models\Central\Tenant::create(['id' => $id]);
        $this->tenant->domains()->create(['domain' => $id.'.localhost']);

        tenancy()->initialize($this->tenant);
    }

    protected function tearDown(): void
    {
        if ($this->tenant) {
            $this->tenant->delete();
        }
        parent::tearDown();
    }

    public function test_property_can_be_created_with_image()
    {
        Storage::fake('public');
        Storage::fake('local');

        $user = User::factory()->create();
        $this->actingAs($user);

        // Mock FilePond facade to return a fake file path
        Filepond::shouldReceive('field')
            ->once()
            ->with('some-server-id')
            ->andReturnSelf();

        Filepond::shouldReceive('moveTo')
            ->once()
            ->andReturn(['filepath' => 'properties/images/1/test.jpg']);

        $this->withoutExceptionHandling();

        $url = 'http://'.$this->tenant->domains->first()->domain.route('admin.properties.store', [], false);
        dump($url);

        $response = $this->post($url, [
            'name' => 'Test Property',
            'address' => 'Test Address',
            'description' => 'Test Description',
            'image' => 'some-server-id',
        ]);

        $response->assertRedirect(route('admin.properties.index'));

        $this->assertDatabaseHas('properties', [
            'name' => 'Test Property',
            'image' => 'properties/images/1/test.jpg',
        ]);
    }

    public function test_property_can_be_updated_with_image()
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $this->actingAs($user);

        $property = Property::factory()->create([
            'image' => 'old-image.jpg',
        ]);

        // Mock FilePond facade
        Filepond::shouldReceive('field')
            ->once()
            ->with('new-server-id')
            ->andReturnSelf();

        Filepond::shouldReceive('moveTo')
            ->once()
            ->andReturn(['filepath' => 'properties/images/'.$property->id.'/new.jpg']);

        $url = 'http://'.$this->tenant->domains->first()->domain.route('admin.properties.update', $property, false);

        $response = $this->put($url, [
            'name' => 'Updated Property',
            'address' => 'Updated Address',
            'description' => 'Updated Description',
            'image' => 'new-server-id',
        ]);

        $response->assertRedirect(route('admin.properties.index'));

        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
            'name' => 'Updated Property',
            'image' => 'properties/images/'.$property->id.'/new.jpg',
        ]);
    }
}
