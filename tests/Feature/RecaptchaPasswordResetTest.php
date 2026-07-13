<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class RecaptchaPasswordResetTest extends TestCase
{
    use RefreshDatabase;

    /** Enable reCAPTCHA with dummy keys via the settings store. */
    private function enableRecaptcha(): void
    {
        Setting::put('recaptcha_enabled', '1');
        Setting::put('recaptcha_site_key', 'test-site-key');
        Setting::put('recaptcha_secret_key', 'test-secret-key');
    }

    public function test_forgot_password_page_renders(): void
    {
        $this->get('/forgot-password')->assertOk()->assertSee('FORGOT PASSWORD');
    }

    public function test_reset_link_email_is_sent(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'link@test.com']);

        $this->post('/forgot-password', ['email' => 'link@test.com'])
            ->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_password_can_be_reset(): void
    {
        $user = User::factory()->create(['email' => 'reset@test.com']);
        $token = Password::createToken($user);

        $this->post('/reset-password', [
            'token' => $token,
            'email' => 'reset@test.com',
            'password' => 'new-secret',
            'password_confirmation' => 'new-secret',
        ])->assertRedirect('/login');

        $this->assertTrue(Hash::check('new-secret', $user->fresh()->password));
    }

    public function test_reset_fails_with_invalid_token(): void
    {
        $user = User::factory()->create(['email' => 'reset2@test.com']);

        $this->from('/reset-password/bad-token')
            ->post('/reset-password', [
                'token' => 'bad-token',
                'email' => 'reset2@test.com',
                'password' => 'new-secret',
                'password_confirmation' => 'new-secret',
            ])->assertSessionHasErrors('email');

        $this->assertFalse(Hash::check('new-secret', $user->fresh()->password));
    }

    public function test_admin_can_update_recaptcha_settings(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->patch('/admin/settings/recaptcha', [
            'recaptcha_enabled' => '1',
            'recaptcha_site_key' => 'site-123',
            'recaptcha_secret_key' => 'secret-123',
        ])->assertRedirect('/admin/settings');

        $this->assertDatabaseHas('glo_settings', ['key' => 'recaptcha_enabled', 'value' => '1']);
        $this->assertDatabaseHas('glo_settings', ['key' => 'recaptcha_site_key', 'value' => 'site-123']);
        $this->assertDatabaseHas('glo_settings', ['key' => 'recaptcha_secret_key', 'value' => 'secret-123']);
    }

    public function test_login_without_recaptcha_still_works_when_disabled(): void
    {
        User::factory()->create(['email' => 'user@test.com']);

        $this->post('/login', ['email' => 'user@test.com', 'password' => '1234'])
            ->assertRedirect('/user/dashboard');
    }

    public function test_login_is_blocked_without_recaptcha_when_enabled(): void
    {
        $this->enableRecaptcha();
        User::factory()->create(['email' => 'user@test.com']);

        $this->from('/login')
            ->post('/login', ['email' => 'user@test.com', 'password' => '1234'])
            ->assertRedirect('/login');

        $this->assertGuest();
    }

    public function test_login_succeeds_with_valid_recaptcha_when_enabled(): void
    {
        $this->enableRecaptcha();
        Http::fake([
            'www.google.com/recaptcha/*' => Http::response(['success' => true, 'score' => 0.9, 'action' => 'login']),
        ]);
        User::factory()->create(['email' => 'user@test.com']);

        $this->post('/login', [
            'email' => 'user@test.com',
            'password' => '1234',
            'g-recaptcha-response' => 'valid-token',
        ])->assertRedirect('/user/dashboard');

        $this->assertAuthenticated();
    }

    public function test_login_is_blocked_when_recaptcha_score_is_too_low(): void
    {
        $this->enableRecaptcha();
        Http::fake([
            'www.google.com/recaptcha/*' => Http::response(['success' => true, 'score' => 0.1, 'action' => 'login']),
        ]);
        User::factory()->create(['email' => 'user@test.com']);

        $this->from('/login')->post('/login', [
            'email' => 'user@test.com',
            'password' => '1234',
            'g-recaptcha-response' => 'low-score-token',
        ])->assertRedirect('/login');

        $this->assertGuest();
    }

    public function test_recaptcha_widget_renders_on_login_when_enabled(): void
    {
        $this->enableRecaptcha();

        $this->get('/login')
            ->assertSee('g-recaptcha-response')
            ->assertSee('test-site-key')
            ->assertSee('grecaptcha.execute');
    }
}
