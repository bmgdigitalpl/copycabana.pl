<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        if (! config('services.admin.email') || ! config('services.admin.password')) {
            return;
        }

        User::query()->updateOrCreate(
            ['email' => config('services.admin.email')],
            ['name' => config('services.admin.name'), 'password' => config('services.admin.password'), 'role' => 'admin'],
        );
    }
}
