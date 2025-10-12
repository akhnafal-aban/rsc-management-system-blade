<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating realistic user data...');

        // Create specific admin users
        User::create([
            'name' => 'aban',
            'email' => 'aban@aban',
            'password' => Hash::make('password'),
            'role' => 'ADMIN',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Manager RSC',
            'email' => 'manager@rsc.com',
            'password' => Hash::make('password'),
            'role' => 'ADMIN',
            'email_verified_at' => now(),
        ]);

        // Create specific staff users
        User::create([
            'name' => 'abanstaff',
            'email' => 'aban@staff',
            'password' => Hash::make('password'),
            'role' => 'STAFF',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Receptionist',
            'email' => 'receptionist@rsc.com',
            'password' => Hash::make('password'),
            'role' => 'STAFF',
            'email_verified_at' => now(),
        ]);

        // Create additional random users
        $adminUsers = User::factory(3)->create([
            'role' => 'ADMIN',
        ]);

        $staffUsers = User::factory(8)->create([
            'role' => 'STAFF',
        ]);

        $this->command->info('User data created successfully!');
        $this->command->info('Created '.$adminUsers->count().' admin users and '.$staffUsers->count().' staff users');
    }
}
