<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Used by the Docker entrypoint: seed demo data on the first start only, so changes
// made while trying the app survive container restarts.
Artisan::command('demo:seed-if-empty', function () {
    if (User::query()->exists()) {
        $this->info('Database already has users, skipping demo seed.');

        return;
    }

    $this->call('db:seed', ['--force' => true]);
})->purpose('Seed demo accounts and equipment when the database is empty');
