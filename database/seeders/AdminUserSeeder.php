<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        if (! env('ADMIN_EMAIL') || ! env('ADMIN_PASSWORD')) {
            return;
        }

        User::query()->updateOrCreate(
            ['email' => env('ADMIN_EMAIL')],
            ['name' => env('ADMIN_NAME', 'CopyCabana Admin'), 'password' => env('ADMIN_PASSWORD'), 'role' => 'admin'],
        );
    }
}
