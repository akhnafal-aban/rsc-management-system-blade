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
            'name' => 'Resha',
            'email' => 'resha@gmail.com',
            'password' => Hash::make('master12sc'),
            'role' => 'ADMIN',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Aban',
            'email' => 'akhnafal03@gmail.com',
            'password' => Hash::make('developer12sc'),
            'role' => 'ADMIN',
            'email_verified_at' => now(),
        ]);

        // Create specific staff users
        User::create([
            'name' => 'Reyhan',
            'email' => 'reyhan@gmail.com',
            'password' => Hash::make('staff12sc1'),
            'role' => 'STAFF',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Dzaky',
            'email' => 'dzaky@gmail.com',
            'password' => Hash::make('staff12sc2'),
            'role' => 'STAFF',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Adlan',
            'email' => 'adlan@gmail.com',
            'password' => Hash::make('staff12sc3'),
            'role' => 'STAFF',
            'email_verified_at' => now(),
        ]);

        // Create additional random users
        // $adminUsers = User::factory(3)->create([
        //     'role' => 'ADMIN',
        // ]);

        // $staffUsers = User::factory(8)->create([
        //     'role' => 'STAFF',
        // ]);

        $this->command->info('User data created successfully!');
        // $this->command->info('Created '.$adminUsers->count().' admin users and '.$staffUsers->count().' staff users');
    }
}
