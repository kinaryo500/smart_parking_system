<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kendaraan;
use App\Models\ParkirTransaksi;
use App\Models\Tarif;
use Carbon\Carbon;

class PasienDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $kendaraan = Kendaraan::where('user_id', $user->id)->get();

        $parkirAktif = ParkirTransaksi::with(['kendaraan'])
            ->where('user_id', $user->id)
            ->where('status', 'aktif')
            ->latest()
            ->get()
            ->map(function ($trx) {

                $waktuMasuk = Carbon::parse($trx->waktu_masuk);
                $sekarang = now();

                $durasiMenit = $waktuMasuk->diffInMinutes($sekarang);

                $trx->estimasi_durasi = $durasiMenit;
                $trx->estimasi_biaya = 0; 

                $trx->slot_label = $trx->slot_code ?? '-';

                return $trx;
            });

        $kendaraanSedangParkirIds = $parkirAktif->pluck('kendaraan_id')->toArray();

        $kendaraanTersedia = $kendaraan
            ->whereNotIn('id', $kendaraanSedangParkirIds)
            ->values();

        $tarifs = Tarif::all();

        $totalPengeluaran = 0;

        return view('pasien.dashboard', compact(
            'user',
            'parkirAktif',
            'kendaraan',
            'kendaraanTersedia',
            'tarifs',
            'totalPengeluaran'
        ));
    }
}