<?php

namespace Database\Seeders;

use App\Models\KantungParkir;
use App\Models\Kendaraan;
use App\Models\ParkirTransaksi;
use App\Models\SlotParkir;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {

        $this->call(TarifSeeder::class);
        // $this->call(QRParkirSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(KendaraanSeeder::class);
        $this->call(GateSeeder::class);
        $this->call(ParkirTransaksiSeeder::class);

    }
}
