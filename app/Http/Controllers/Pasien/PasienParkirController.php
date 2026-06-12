<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use App\Models\Kendaraan;
use App\Models\QRParkir;
use App\Models\ParkirTransaksi;
use App\Models\Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Events\QRUpdated;
use App\Services\MqttService;

class PasienParkirController extends Controller
{
    public function scanIndex($kendaraanId)
    {
        $user = auth()->user();

        $kendaraan = Kendaraan::where('id', $kendaraanId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        return view('pasien.parkir.scan', compact('kendaraan'));
    }

    public function detail($id)
    {
        $transaksi = ParkirTransaksi::with(['kendaraan', 'qrParkir'])
            ->where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if ($transaksi->status === 'aktif') {
            $menit = Carbon::parse($transaksi->waktu_masuk)->diffInMinutes(now());

            $transaksi->estimasi_durasi = $menit;
            $transaksi->estimasi_biaya = 0; // Pasien diset Free
        }

        return view('pasien.parkir.detail', compact('transaksi'));
    }

    public function scanStore(Request $request)
    {
        $request->validate([
            'qr_kode'      => 'required|string',
            'kendaraan_id' => 'required|exists:kendaraans,id'
        ]);

        $user = Auth::user();

        $kendaraan = Kendaraan::where('id', $request->kendaraan_id)
            ->where('user_id', $user->id)
            ->first();

        if (!$kendaraan) {
            return response()->json([
                'success' => false,
                'message' => 'Data kendaraan tidak ditemukan'
            ], 404);
        }

        $cekKendaraanAktif = ParkirTransaksi::where('kendaraan_id', $kendaraan->id)
            ->where('status', 'aktif')
            ->exists();

        if ($cekKendaraanAktif) {
            return response()->json([
                'success' => false,
                'message' => 'Kendaraan masih di dalam area parkir'
            ], 422);
        }

        $qr = QRParkir::where('kode', $request->qr_kode)->first();

        if (!$qr || !$qr->aktif || $qr->status !== 'tersedia') {
            return response()->json([
                'success' => false,
                'message' => 'Kode QR tidak valid atau sudah kadaluarsa'
            ], 422);
        }

        DB::beginTransaction();

        try {
            $gateMasuk = Gate::where('tipe_gate', 'masuk')->first();

            $transaksi = ParkirTransaksi::create([
                'user_id'         => $user->id,
                'kendaraan_id'    => $kendaraan->id,
                'gate_masuk_id'   => $gateMasuk?->id,
                'qr_parkir_id'    => $qr->id,
                'waktu_masuk'     => now(),
                'jenis_kendaraan' => $kendaraan->jenis,
                'tarif_per_jam'   => 0, // Set tarif 0 untuk pasien
                'status'          => 'aktif'
            ]);

            $qr->update([
                'status' => 'terpakai',
                'aktif'  => false
            ]);

            do {
                $kodeBaru = 'PKR-' . Str::upper(Str::random(8));
            } while (QRParkir::where('kode', $kodeBaru)->exists());

            QRParkir::create([
                'kode'   => $kodeBaru,
                'status' => 'tersedia',
                'aktif'  => true
            ]);

            $mqttPayload = [
                'id'      => uniqid('cmd_'),
                'command' => 'OPEN_GATE_ENTRY',
                'payload' => [
                    'transaksi_id' => $transaksi->id,
                    'plat_nomor'   => $kendaraan->plat_nomor
                ],
                'qr'   => $kodeBaru,
                'time' => now()->toDateTimeString()
            ];

            MqttService::publish("smartparking/univ123/commands", $mqttPayload);

            broadcast(new QRUpdated([
                'kode'   => $kodeBaru,
                'status' => 'tersedia',
                'time'   => now()->format('H:i:s')
            ]));

            DB::commit();

            return response()->json([
                'success'  => true,
                'message'  => 'Gate berhasil dibuka!',
                // Pastikan route name `pasien.parkir.detail` terdaftar di web.php Anda
                'redirect' => route('pasien.parkir.detail', $transaksi->id) 
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Pasien Scan MQTT Error: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getStatusApi($id)
    {
        $transaksi = ParkirTransaksi::find($id);

        if (!$transaksi) {
            return response()->json(['message' => 'Not Found'], 404);
        }

        $waktuMasuk = Carbon::parse($transaksi->waktu_masuk);
        $waktuKeluar = $transaksi->waktu_keluar ? Carbon::parse($transaksi->waktu_keluar) : now();
        $totalMenit = $waktuMasuk->diffInMinutes($waktuKeluar);

        return response()->json([
            'status' => $transaksi->status,
            'waktu_keluar' => $transaksi->waktu_keluar
                ? Carbon::parse($transaksi->waktu_keluar)->format('H:i')
                : '--:--',
            'durasi_teks' => floor($totalMenit / 60) . " jam " . ($totalMenit % 60) . " menit",
            'total_bayar_formatted' => '0 (Free)' 
        ]);
    }
}