<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TenantTestCase;

class DashboardTest extends TenantTestCase
{

    public function test_guests_are_redirected_to_the_login_page()
    {
        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_dashboard()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.dashboard'));
        $response->assertStatus(200);
    }

    public function test_dashboard_displays_bookings()
    {
        $user = User::factory()->create();
        $property = \App\Models\Property::factory()->create();
        $booking = \App\Models\Booking::create([
            'property_id' => $property->id,
            'user_id' => $user->id,
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(3),
            'total_price' => 200,
            'status' => 'confirmed',
        ]);

        $this->actingAs($user);

        $response = $this->get(route('admin.dashboard'));
        $response->assertStatus(200);
        $response->assertInertia(
            fn($page) => $page
                ->component('Admin/Dashboard')
                ->has('bookings', 1)
                ->where('bookings.0.key', $booking->id)
        );
    }
}
