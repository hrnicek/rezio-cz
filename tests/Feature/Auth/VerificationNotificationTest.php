<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class VerificationNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_sends_verification_notification(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        $this->actingAs($user);

        $this->get(route('verification.notice')); // Visit page to get CSRF token

        $response = $this->post(route('verification.send'), [
            '_token' => csrf_token(), // Manually include CSRF token
        ])->assertRedirect(route('verification.notice'));

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_does_not_send_verification_notification_if_email_is_verified(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->actingAs($user);

        $this->get(route('verification.notice')); // Visit page to get CSRF token

        $response = $this->post(route('verification.send'), [
            '_token' => csrf_token(),
        ]);

        $response->assertRedirect(route('admin.dashboard'));

        Notification::assertNothingSent();
    }
}
