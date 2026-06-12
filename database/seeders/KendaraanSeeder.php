<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Kendaraan;

class KendaraanSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::whereIn('role', ['user', 'pasien', 'pegawai'])->get();

        foreach ($users as $user) {
            $jumlahKendaraan = rand(1, 5);

            for ($i = 1; $i <= $jumlahKendaraan; $i++) {
                $jenis = rand(0, 1) ? 'motor' : 'mobil';

                Kendaraan::create([
                    'user_id' => $user->id,
                    'jenis' => $jenis,
                    'plat_nomor' => $this->generatePlat(),
                    'merk' => $jenis === 'motor' ? collect(['Honda', 'Yamaha', 'Suzuki'])->random() : collect(['Toyota', 'Daihatsu', 'Mitsubishi'])->random(),
                    'warna' => collect(['Hitam', 'Putih', 'Silver', 'Merah', 'Biru'])->random(),
                ]);
            }
        }
    }

    private function generatePlat()
    {
        return 'B ' . rand(1000, 9999) . ' ' . chr(rand(65, 90)) . chr(rand(65, 90));
    }
}