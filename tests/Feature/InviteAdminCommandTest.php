<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\AdminInvitation;
use App\Notifications\AdminResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class InviteAdminCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_an_admin_and_emails_a_link_to_set_the_password(): void
    {
        Notification::fake();

        $this->artisan('admin:invite', ['email' => 'Nowy@Example.com'])
            ->expectsOutputToContain('Invitation sent to nowy@example.com.')
            ->assertSuccessful();

        $admin = User::query()->where('email', 'nowy@example.com')->firstOrFail();
        $this->assertSame('admin', $admin->role);
        $this->assertTrue($admin->isOwner());
        $this->assertNotNull($admin->email_verified_at);

        Notification::assertSentTo($admin, AdminInvitation::class, function (AdminInvitation $notification) use ($admin): bool {
            $mail = $notification->toMail($admin);

            return $mail->actionText === 'Wygeneruj hasło'
                && $mail->actionUrl === url(route('password.reset', [
                    'token' => $admin->id.'.'.$notification->token,
                ], false))
                && ! str_contains($mail->actionUrl, 'email');
        });
    }

    public function test_the_invited_admin_can_set_a_password_with_the_emailed_token(): void
    {
        Notification::fake();
        $this->artisan('admin:invite', ['email' => 'nowy@example.com'])->assertSuccessful();

        $admin = User::query()->where('email', 'nowy@example.com')->firstOrFail();
        $token = null;
        Notification::assertSentTo($admin, AdminInvitation::class, function (AdminInvitation $notification) use (&$token): bool {
            $token = $notification->token;

            return true;
        });

        $this->get(route('password.reset', ['token' => $admin->id.'.'.$token]))
            ->assertOk()
            ->assertSee('<input type="hidden" name="email" value="nowy@example.com">', false)
            ->assertSee('<input type="hidden" name="token" value="'.$token.'">', false);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => 'nowy@example.com',
            'password' => 'bardzo-tajne-haslo-1',
            'password_confirmation' => 'bardzo-tajne-haslo-1',
        ])->assertRedirect(route('dashboard'))->assertSessionHas('status', 'Hasło zostało ustawione.');

        $this->assertAuthenticatedAs($admin->fresh());
        $this->assertTrue(Hash::check('bardzo-tajne-haslo-1', $admin->fresh()->password));
    }

    public function test_the_admin_password_reset_link_does_not_expose_the_email(): void
    {
        Notification::fake();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->post(route('password.email'), ['email' => $admin->email])->assertSessionHasNoErrors();

        Notification::assertSentTo($admin, AdminResetPassword::class, function (AdminResetPassword $notification) use ($admin): bool {
            $url = $notification->toMail($admin)->actionUrl;

            return str_contains($url, '/'.$admin->id.'.'.$notification->token) && ! str_contains($url, 'email');
        });
    }

    public function test_a_tampered_reset_link_does_not_reveal_an_email(): void
    {
        $this->get(route('password.reset', ['token' => 'nie-token']))
            ->assertOk()
            ->assertSee('<input type="hidden" name="email" value="">', false);
    }

    public function test_running_it_again_resends_the_invitation_without_duplicating_the_account(): void
    {
        Notification::fake();

        $this->artisan('admin:invite', ['email' => 'nowy@example.com'])->assertSuccessful();
        $this->artisan('admin:invite', ['email' => 'nowy@example.com'])->assertSuccessful();

        $this->assertSame(1, User::query()->where('email', 'nowy@example.com')->count());
        Notification::assertSentToTimes(User::query()->firstOrFail(), AdminInvitation::class, 2);
    }

    public function test_it_refuses_an_email_that_belongs_to_a_non_admin_account(): void
    {
        Notification::fake();
        User::factory()->create(['email' => 'klient@example.com', 'role' => 'customer']);

        $this->artisan('admin:invite', ['email' => 'klient@example.com'])->assertFailed();

        Notification::assertNothingSent();
    }

    public function test_it_rejects_an_invalid_email(): void
    {
        Notification::fake();

        $this->artisan('admin:invite', ['email' => 'nie-email'])->assertFailed();

        $this->assertDatabaseCount('users', 0);
        Notification::assertNothingSent();
    }
}
