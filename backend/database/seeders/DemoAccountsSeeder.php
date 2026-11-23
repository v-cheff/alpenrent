<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Demo accounts for local development — one per role. Listed in the README.
 */
class DemoAccountsSeeder extends Seeder
{
    public const PASSWORD = 'password';

    public const RENTER_EMAIL = 'renter@example.com';

    public const OWNER_EMAIL = 'owner@example.com';

    public function run(): void
    {
        $this->account(self::RENTER_EMAIL, 'Anna Huber', User::ROLE_RENTER);
        $this->account(self::OWNER_EMAIL, 'Tirol Ski Rent', User::ROLE_OWNER);
    }

    private function account(string $email, string $name, string $role): User
    {
        return User::query()->updateOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => Hash::make(self::PASSWORD), 'role' => $role],
        );
    }
}
