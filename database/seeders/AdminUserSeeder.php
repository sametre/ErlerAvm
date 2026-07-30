<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Create or update the first admin only when credentials are explicitly configured.
     */
    public function run(): void
    {
        $email = env('ADMIN_USER_EMAIL');
        $password = env('ADMIN_USER_PASSWORD');

        if (! is_string($email) || ! is_string($password) || $email === '' || $password === '') {
            return;
        }

        $user = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => env('ADMIN_USER_NAME', 'Erler AVM Admin'),
                'password' => Hash::make($password),
                'is_admin' => true,
                'email_verified_at' => now(),
            ],
        );

        $user->forceFill([
            'is_admin' => true,
        ])->save();

        $user->syncRoles(['Super Admin']);
    }
}
