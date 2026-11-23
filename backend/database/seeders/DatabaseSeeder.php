<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Demo data for a fresh install: `php artisan migrate:fresh --seed`.
     * One renter, one owner with two rental points and equipment at each.
     */
    public function run(): void
    {
        $this->call([
            DemoAccountsSeeder::class,
            EquipmentSeeder::class,
        ]);
    }
}
