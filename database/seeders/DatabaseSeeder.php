<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
{
    User::firstOrCreate(
        ['email' => 'admin@example.com'],
        [
            'name' => 'Admin',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]
    );

    $this->call([
        CategorySeeder::class,
        ProductSeeder::class,
        OrderSeeder::class,
        OrderItemSeeder::class,
    ]);
}
}
