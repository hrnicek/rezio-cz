<?php

namespace Tests\Feature;

use App\Mail\BookingConfirmation;
use App\Models\Booking;
use App\Models\EmailTemplate;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_list_email_templates_for_a_property()
    {
        $user = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $user->id]);
        $template = EmailTemplate::create([
            'property_id' => $property->id,
            'type' => 'booking_confirmation',
            'subject' => 'Custom Subject',
            'body' => 'Custom Body',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('admin.properties.email-templates.index', $property));

        $response->assertStatus(200);
        $response->assertInertia(
            fn($page) => $page
                ->component('Admin/Properties/EmailTemplates/Index')
                ->has('templates', 1)
                ->where('templates.0.subject', 'Custom Subject')
        );
    }

    public function test_it_can_create_an_email_template()
    {
        $user = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post(route('admin.properties.email-templates.store', $property), [
            'type' => 'booking_confirmation',
            'subject' => 'New Subject',
            'body' => 'New Body',
            'is_active' => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('email_templates', [
            'property_id' => $property->id,
            'type' => 'booking_confirmation',
            'subject' => 'New Subject',
        ]);
    }

    public function test_it_can_update_an_email_template()
    {
        $user = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $user->id]);
        $template = EmailTemplate::create([
            'property_id' => $property->id,
            'type' => 'booking_confirmation',
            'subject' => 'Old Subject',
            'body' => 'Old Body',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->put(route('admin.properties.email-templates.update', [$property, $template]), [
            'subject' => 'Updated Subject',
            'body' => 'Updated Body',
            'is_active' => false,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('email_templates', [
            'id' => $template->id,
            'subject' => 'Updated Subject',
            'is_active' => false,
        ]);
    }

    public function test_booking_confirmation_mailable_uses_custom_template()
    {
        $property = Property::factory()->create();
        $booking = Booking::factory()->create(['property_id' => $property->id]);

        // Create a custom template
        EmailTemplate::create([
            'property_id' => $property->id,
            'type' => 'booking_confirmation',
            'subject' => 'Custom Subject {{ booking_code }}',
            'body' => 'Hello {{ customer_name }}, your booking {{ booking_code }} at {{ property_name }} is confirmed.',
            'is_active' => true,
        ]);

        $mailable = new BookingConfirmation($booking);

        $this->assertEquals('Custom Subject ' . $booking->code, $mailable->envelope()->subject);

        $mailable->assertSeeInHtml('Hello ' . $booking->customer->first_name . ' ' . $booking->customer->last_name);
        $mailable->assertSeeInHtml('your booking ' . $booking->code);
        $mailable->assertSeeInHtml('at ' . $property->name);
    }

    public function test_booking_confirmation_mailable_falls_back_to_default_if_no_template()
    {
        $property = Property::factory()->create();
        $booking = Booking::factory()->create(['property_id' => $property->id]);

        $mailable = new BookingConfirmation($booking);

        $this->assertEquals('Booking Confirmation - ' . $property->name, $mailable->envelope()->subject);
        // Default view content check (assuming default view has specific text)
        // $mailable->assertSeeInHtml('Booking Confirmation'); 
    }
}
