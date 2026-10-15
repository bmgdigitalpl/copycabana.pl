<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\AdminInvitation;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

#[Signature('admin:invite {email : E-mail of the new administrator}')]
#[Description('Create an administrator account and email an invitation to set the password')]
class InviteAdmin extends Command
{
    public function handle(): int
    {
        $email = Str::lower(trim((string) $this->argument('email')));

        if (Validator::make(['email' => $email], ['email' => ['required', 'email']])->fails()) {
            $this->error("\"{$email}\" is not a valid e-mail address.");

            return self::FAILURE;
        }

        $user = User::query()->where('email', $email)->first();

        if ($user !== null && $user->role !== 'admin') {
            $this->error("An account with {$email} already exists and is not an administrator.");

            return self::FAILURE;
        }

        $user ??= User::query()->forceCreate([
            'name' => Str::headline(Str::before($email, '@')),
            'email' => $email,
            'password' => Str::random(64),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $user->notify(new AdminInvitation(Password::createToken($user)));

        $this->info("Invitation sent to {$email}.");

        return self::SUCCESS;
    }
}
