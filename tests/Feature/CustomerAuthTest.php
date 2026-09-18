<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use App\Notifications\CustomerResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class CustomerAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_a_customer_account_and_sends_verification(): void
    {
        Notification::fake();

        $response = $this->post(route('customer.register.store'), [
            'name' => 'Jan Kowalski',
            'email' => 'jan@example.com',
            'password' => 'Strong-password-123!',
            'password_confirmation' => 'Strong-password-123!',
            'privacy_policy_accepted' => true,
        ]);

        $user = User::query()->where('email', 'jan@example.com')->firstOrFail();

        $response->assertRedirect(route('verification.notice'));
        $this->assertDatabaseHas('clients', [
            'email' => 'jan@example.com',
            'privacy_policy_version' => config('privacy.policy_version'),
        ]);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'role' => 'customer',
            'client_id' => $user->client_id,
        ]);
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_registration_requires_privacy_acceptance(): void
    {
        $response = $this->post(route('customer.register.store'), [
            'name' => 'Jan Kowalski',
            'email' => 'jan@example.com',
            'password' => 'Strong-password-123!',
            'password_confirmation' => 'Strong-password-123!',
        ]);

        $response->assertInvalid('privacy_policy_accepted');
        $this->assertDatabaseCount('users', 0);
    }

    public function test_unverified_customer_is_sent_to_the_verification_notice_after_login(): void
    {
        $client = Client::factory()->create(['email' => 'jan@example.com']);
        $user = User::factory()->unverified()->create([
            'email' => 'jan@example.com',
            'role' => 'customer',
            'client_id' => $client->id,
        ]);

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('verification.notice'));
    }

    public function test_verified_customer_can_log_in_to_the_portal(): void
    {
        $client = Client::factory()->create(['email' => 'jan@example.com']);
        $user = User::factory()->create([
            'email' => 'jan@example.com',
            'role' => 'customer',
            'client_id' => $client->id,
        ]);

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('customer.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_is_redirected_to_the_dashboard_after_logging_in_through_the_shared_screen(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_customer_verification_marks_the_email_as_verified(): void
    {
        $client = Client::factory()->create(['email' => 'jan@example.com']);
        $user = User::factory()->unverified()->create([
            'email' => 'jan@example.com',
            'role' => 'customer',
            'client_id' => $client->id,
        ]);
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(30),
            ['id' => $user->id, 'hash' => sha1($user->getEmailForVerification())],
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        $response->assertRedirect(route('customer.dashboard'));
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_guest_is_redirected_to_customer_login_from_the_portal(): void
    {
        $this->get(route('customer.dashboard'))->assertRedirect(route('login'));
    }

    public function test_customer_password_reset_uses_the_customer_reset_url(): void
    {
        Notification::fake();
        $client = Client::factory()->create(['email' => 'jan@example.com']);
        $user = User::factory()->create([
            'email' => 'jan@example.com',
            'role' => 'customer',
            'client_id' => $client->id,
        ]);

        $this->post(route('customer.password.email'), ['email' => $user->email])
            ->assertRedirect()
            ->assertSessionHas('status');

        Notification::assertSentTo($user, CustomerResetPassword::class, function (CustomerResetPassword $notification) use ($user): bool {
            return $notification->toMail($user)->actionUrl === url(route('customer.password.reset', [
                'token' => $notification->token,
                'email' => $user->email,
            ], false));
        });
    }

    public function test_customer_password_reset_cannot_change_an_administrator_password(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'role' => 'admin',
            'password' => 'password',
        ]);
        $token = Password::broker()->createToken($user);

        $this->post(route('customer.password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'Strong-password-123!',
            'password_confirmation' => 'Strong-password-123!',
        ])->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }
}
