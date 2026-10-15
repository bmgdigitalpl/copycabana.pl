<?php

namespace App\Models;

use App\Notifications\AdminResetPassword;
use App\Notifications\CustomerResetPassword;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;

#[Fillable(['name', 'email', 'password', 'role', 'client_id'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail, PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'disabled_at' => 'datetime',
        ];
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'staff'], true) && $this->disabled_at === null;
    }

    public function isOwner(): bool
    {
        return $this->role === 'admin' && $this->disabled_at === null;
    }

    public function isCustomer(): bool
    {
        return $this->role === 'customer' && $this->client_id !== null && $this->disabled_at === null;
    }

    public function initials(): string
    {
        $initials = Str::initials($this->name, true);

        return Str::length($initials) > 1
            ? Str::substr($initials, 0, 1).Str::substr($initials, -1)
            : $initials;
    }

    public function sendPasswordResetNotification(#[\SensitiveParameter] $token): void
    {
        if ($this->role === 'customer') {
            $this->notify(new CustomerResetPassword($token));

            return;
        }

        $this->notify(new AdminResetPassword($token));
    }

    /**
     * The password reset link carries the user id in front of the token, so the
     * address is not exposed in the query string.
     */
    public function adminPasswordResetUrl(string $token): string
    {
        return url(route('password.reset', ['token' => $this->getKey().'.'.$token], false));
    }

    /**
     * @return array{email: string|null, token: string}
     */
    public static function resolvePasswordResetToken(string $routeToken): array
    {
        [$userId, $token] = array_pad(explode('.', $routeToken, 2), 2, '');

        return [
            'email' => ctype_digit($userId) && $token !== ''
                ? static::query()->whereKey((int) $userId)->value('email')
                : null,
            'token' => $token,
        ];
    }

    /** @return BelongsTo<Client, $this> */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
