<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ParkirTransaksi;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CetakNotaController extends Controller
{
    public function show($id)
    {
        try {
            $user = Auth::user();

            $trx = ParkirTransaksi::with(['kendaraan', 'petugas'])->findOrFail($id);

            if ($user->role === 'admin') {
            } elseif ($user->role === 'petugas') {
                if ($trx->petugas_id !== $user->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda tidak memiliki akses ke transaksi ini'
                    ], 403);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak memiliki izin'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'kode_qr'       => $trx->qrParkir->kode ?? '-',
                    'plat_nomor'    => $trx->kendaraan->plat_nomor ?? '-',
                    'jenis'         => $trx->jenis_kendaraan,
                    'waktu_masuk'   => $trx->waktu_masuk,
                    'waktu_keluar'  => $trx->waktu_keluar,
                    'total_waktu'   => $trx->total_waktu,
                    'total_bayar'   => $trx->total_bayar,
                    'petugas'       => $trx->petugas->name ?? '-',
                ],
                'settings' => [
                    'app_name'      => Setting::where('key', 'app_name')->first()?->value ?? 'SMART PARKING',
                    'lokasi_parkir' => Setting::where('key', 'lokasi_parkir')->first()?->value ?? '-',
                    'alamat'        => Setting::where('key', 'alamat')->first()?->value ?? '-',
                    'kontak'        => Setting::where('key', 'kontak')->first()?->value ?? '-',
                ]
            ]);
        } catch (\Exception $e) {
            Log::error("Cetak Nota Error: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data'
            ], 500);
        }
    }
}
