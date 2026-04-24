<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {

        User::create([
            'name' => 'Admin Smart Parking',
            'email' => 'admin@smartparking.test',
            'no_hp' => '081111111111',
            'role' => 'admin',
            'password' => Hash::make('password'),
        ]);


        for ($i = 1; $i <= 1; $i++) {
            User::create([
                'name' => "Petugas {$i}",
                'email' => "petugas{$i}@smartparking.test",
                'no_hp' => "08222222222{$i}",
                'role' => 'petugas',
                'password' => Hash::make('password'),
            ]);
        }

        
            User::create([
                'name' => "User {$i}",
                'email' => "user1@smartparking.test",
                'no_hp' => "08333333333{$i}",
                'role' => 'user',
                'password' => Hash::make('password'),
            ]);
        
    }
}
