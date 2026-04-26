<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ParkirTransaksi;
use App\Models\Kendaraan;
use App\Models\Tarif;
use App\Models\Gate;
use App\Models\User;
use App\Models\QRParkir;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ParkirTransaksiSeeder extends Seeder
{
    public function run(): void
    {
        $gateMasuk = Gate::where('tipe_gate', 'masuk')->first();
        $petugasList = User::where('role', 'petugas')->get();
        $allKendaraan = Kendaraan::all();

        if ($allKendaraan->isEmpty() || !$gateMasuk || $petugasList->isEmpty()) {
            $this->command->warn('Data kendaraan / gate / petugas belum lengkap!');
            return;
        }

        for ($i = 0; $i < 40; $i++) {
            $this->createTransaksi($allKendaraan->random(), $petugasList->random(), $gateMasuk, 'selesai', true);
        }

        for ($i = 0; $i < 10; $i++) {
            $this->createTransaksi($allKendaraan->random(), $petugasList->random(), $gateMasuk, 'selesai', false);
        }

        $kendaraanTersedia = Kendaraan::whereDoesntHave('parkirTransaksis', function ($query) {
            $query->where('status', 'aktif');
        })->get();

        if ($kendaraanTersedia->isNotEmpty()) {
            $jumlahAktif = min(5, $kendaraanTersedia->count());
            $kendaraanUntukAktif = $kendaraanTersedia->random($jumlahAktif);

            foreach ($kendaraanUntukAktif as $kendaraan) {
                $this->createTransaksi($kendaraan, $petugasList->random(), $gateMasuk, 'aktif', false);
            }
        }

        QRParkir::create([
            'kode' => 'PKR-' . strtoupper(Str::random(6)),
            'status' => 'tersedia',
            'aktif' => true
        ]);

        $this->command->info('Seeder transaksi berhasil dijalankan.');
    }

    private function createTransaksi($kendaraan, $petugas, $gate, $status, $isOldData)
    {
        $tarif = Tarif::where('nama', strtolower($kendaraan->jenis))->first();
        $tarifPerJam = $tarif->tarif_per_jam ?? 2000;

        $qr = QRParkir::create([
            'kode' => 'PKR-' . strtoupper(Str::random(6)),
            'status' => ($status === 'aktif') ? 'terpakai' : 'tersedia',
            'aktif' => true
        ]);

        $waktuMasuk = $isOldData 
            ? Carbon::now()->subDays(rand(1, 60))->subMinutes(rand(0, 1440))
            : Carbon::now()->subMinutes(rand(10, 300));

        if ($status === 'selesai') {
            $durasiMenit = rand(10, 300);
            $waktuKeluar = (clone $waktuMasuk)->addMinutes($durasiMenit);
            $totalBayar = max(1, ceil($durasiMenit / 60)) * $tarifPerJam;
        } else {
            $waktuKeluar = null;
            $durasiMenit = null;
            $totalBayar = 0;
        }

        ParkirTransaksi::create([
            'user_id'         => $kendaraan->user_id,
            'petugas_id'      => $petugas->id,
            'kendaraan_id'    => $kendaraan->id,
            'gate_masuk_id'   => $gate->id,
            'gate_keluar_id'  => $status === 'selesai' ? $gate->id : null,
            'qr_parkir_id'    => $qr->id,
            'waktu_masuk'     => $waktuMasuk,
            'waktu_keluar'    => $waktuKeluar,
            'total_waktu'     => $durasiMenit,
            'jenis_kendaraan' => $kendaraan->jenis,
            'tarif_per_jam'   => $tarifPerJam,
            'total_bayar'     => $totalBayar,
            'status'          => $status,
        ]);
    }
}