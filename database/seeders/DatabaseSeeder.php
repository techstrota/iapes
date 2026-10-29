<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
{
    User::updateOrCreate(
        ['email' => 'techstrota@gmail.com'],
        [
            'name' => 'Admin',
            'password' => bcrypt('Tech@admin#2026'),
            'is_admin' => true,
        ]
    );
}
}
