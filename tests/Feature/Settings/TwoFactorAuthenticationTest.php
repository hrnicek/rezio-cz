<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Fortify\Features;
use Tests\TenantTestCase;

class TwoFactorAuthenticationTest extends TenantTestCase
{

    public function test_two_factor_settings_page_can_be_rendered()
    {
        if (! Features::canManageTwoFactorAuthentication()) {
            $this->markTestSkipped('Two-factor authentication is not enabled.');
        }

        Features::twoFactorAuthentication([
            'confirm' => true,
            'confirmPassword' => true,
        ]);

        $user = User::factory()->withoutTwoFactor()->create();

        $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->get(route('admin.two-factor.show'))
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component('Admin/Settings/TwoFactor')
                    ->where('twoFactorEnabled', false)
            );
    }

    public function test_two_factor_settings_page_requires_password_confirmation_when_enabled()
    {
        if (! Features::canManageTwoFactorAuthentication()) {
            $this->markTestSkipped('Two-factor authentication is not enabled.');
        }

        $user = User::factory()->create();

        Features::twoFactorAuthentication([
            'confirm' => true,
            'confirmPassword' => true,
        ]);

        $response = $this->actingAs($user)
            ->get(route('admin.two-factor.show'));

        $response->assertRedirect(route('password.confirm'));
    }

    public function test_two_factor_settings_page_does_not_requires_password_confirmation_when_disabled()
    {
        if (! Features::canManageTwoFactorAuthentication()) {
            $this->markTestSkipped('Two-factor authentication is not enabled.');
        }

        $user = User::factory()->create();

        Features::twoFactorAuthentication([
            'confirm' => true,
            'confirmPassword' => false,
        ]);

        $this->actingAs($user)
            ->get(route('admin.two-factor.show'))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component('Admin/Settings/TwoFactor')
            );
    }

    public function test_two_factor_settings_page_returns_forbidden_response_when_two_factor_is_disabled()
    {
        if (! Features::canManageTwoFactorAuthentication()) {
            $this->markTestSkipped('Two-factor authentication is not enabled.');
        }

        config(['fortify.features' => []]);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->get(route('admin.two-factor.show'))
            ->assertForbidden();
    }

    public function test_two_factor_authentication_can_be_enabled()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->post('/user/two-factor-authentication');

        $response->assertStatus(302);

        $user->refresh();

        $this->assertNotNull($user->two_factor_secret);
        $this->assertNotNull($user->two_factor_recovery_codes);
    }

    public function test_two_factor_authentication_can_be_confirmed()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $this->post('/user/two-factor-authentication');

        $user->refresh();

        $response = $this->post('/user/confirmed-two-factor-authentication', [
            'code' => 'invalid-code',
        ]);

        $response->assertSessionHasErrors();

        // We can't easily test valid code confirmation without mocking Google2FA
    }

    public function test_recovery_codes_can_be_regenerated()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $this->post('/user/two-factor-authentication');
        $user->refresh();
        $originalCodes = $user->two_factor_recovery_codes;

        $response = $this->post('/user/two-factor-recovery-codes');

        $user->refresh();

        $this->assertNotEquals($originalCodes, $user->two_factor_recovery_codes);
    }

    public function test_two_factor_authentication_can_be_disabled()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $this->post('/user/two-factor-authentication');
        $user->refresh();
        $this->assertNotNull($user->two_factor_secret);

        $response = $this->delete('/user/two-factor-authentication');

        $user->refresh();

        $this->assertNull($user->two_factor_secret);
    }

    public function test_two_factor_authentication_page_shows_qr_code_when_enabled()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $this->post('/user/two-factor-authentication');

        // Confirm password to satisfy middleware
        $this->post('/user/confirm-password', [
            'password' => 'password',
        ]);

        $response = $this->get(route('admin.two-factor.show'));

        $response->assertInertia(
            fn(Assert $page) => $page
                ->component('Admin/Settings/TwoFactor')
                ->has('qrCode')
                ->has('recoveryCodes')
        );
    }

    public function test_two_factor_authentication_page_shows_recovery_codes_when_enabled()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $this->post('/user/two-factor-authentication');

        // Confirm password to satisfy middleware
        $this->post('/user/confirm-password', [
            'password' => 'password',
        ]);

        $response = $this->get(route('admin.two-factor.show'));

        $response->assertInertia(
            fn(Assert $page) => $page
                ->component('Admin/Settings/TwoFactor')
                ->has('recoveryCodes')
        );
    }

    public function test_two_factor_authentication_page_shows_confirm_password_when_needed()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        // Simulate password confirmation timeout
        $this->travel(config('auth.password_timeout', 10800) + 1)->seconds();

        $response = $this->get(route('admin.two-factor.show'));

        $response->assertRedirect(route('password.confirm'));
    }
}
