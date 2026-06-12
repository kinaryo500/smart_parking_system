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

        User::create([
            'name' => 'Petugas 1',
            'email' => 'petugas1@smartparking.test',
            'no_hp' => '082222222221',
            'role' => 'petugas',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'Pegawai 1',
            'email' => 'pegawai1@smartparking.test',
            'no_hp' => '084444444441',
            'role' => 'pegawai',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'Pasien 1',
            'email' => 'pasien1@smartparking.test',
            'no_hp' => '085555555551',
            'role' => 'pasien',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'User Umum 1',
            'email' => 'user1@smartparking.test',
            'no_hp' => '083333333331',
            'role' => 'user',
            'password' => Hash::make('password'),
        ]);
    }
}