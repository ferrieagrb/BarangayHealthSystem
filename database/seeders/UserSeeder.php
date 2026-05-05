<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'bhw user',
            'email' => 'bhw@example.com',
            'password' => bcrypt('password123'),
            'role' => 'bhw',
        ]);

        User::create([
            'name' => 'admin user',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'nurse user',
            'email' => 'nurse@example.com',
            'password' => bcrypt('password123'),
            'role' => 'nurse',
        ]);
    }
}