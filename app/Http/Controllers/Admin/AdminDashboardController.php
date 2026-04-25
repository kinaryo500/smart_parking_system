<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ParkirTransaksi;
use App\Models\Gate;
use App\Models\Setting;
use App\Models\QRParkir;
use App\Services\MqttService;
use App\Events\QRUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Illuminate\Support\Str;

class AdminDashboardController extends Controller
{

    public function index()
    {
        return view('admin.dashboard.index');
    }

    public function slots()
    {
        try {
            $slotsFromEsp = Cache::get('esp_slots_status', []);

            $transaksiAktif = ParkirTransaksi::with('kendaraan')
                ->where('status', 'aktif')
                ->orderByDesc('waktu_masuk')
                ->get();

            $processedSlots = [];

            foreach ($slotsFromEsp as $kode => $dataEsp) {
                $statusFisik = $dataEsp['status'] ?? 'kosong';
                $processedSlots[] = [
                    'kode'       => $kode,
                    'status'     => $statusFisik,
                    'jenis'      => $dataEsp['jenis'] ?? 'mobil',
                    'plat'       => $statusFisik === 'terisi' ? 'OCCUPIED' : '-',
                    'updated_at' => now()->format('H:i:s'),
                ];
            }

            $activeTrxData = $transaksiAktif->map(function ($t) {
                return [
                    'id'            => $t->id,
                    'plat'          => $t->kendaraan->plat_nomor ?? 'N/A',
                    'jenis'         => $t->kendaraan->jenis ?? 'mobil',
                    'masuk'         => Carbon::parse($t->waktu_masuk)->format('H:i:s'),
                    'total_waktu'   => (int) $t->hitungDurasi(), // Konsisten dengan migration
                    'tarif'         => $t->tarif_per_jam,
                    'waktu_masuk'   => $t->waktu_masuk
                ];
            });

            return response()->json([
                'areas' => [
                    [
                        'nama'      => 'AREA PARKIR',
                        'kapasitas' => count($processedSlots),
                        'terisi'    => collect($processedSlots)->where('status', 'terisi')->count(),
                        'kosong'    => collect($processedSlots)->where('status', 'kosong')->count(),
                        'slot'      => $processedSlots
                    ]
                ],
                'active_transactions' => $activeTrxData
            ]);
        } catch (\Exception $e) {
            Log::error("Dashboard Slots Error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data dashboard'
            ], 500);
        }
    }


    public function keluar($id)
    {
        Log::info("--- [START] PROSES KELUAR ID: $id ---");

        DB::beginTransaction();
        try {
            $trx = ParkirTransaksi::with(['kendaraan', 'qrParkir'])->findOrFail($id);

            $totalBayar = $trx->hitungTotalBayar();
            $durasiMenit = (int) $trx->hitungDurasi();

            Log::info("Transaksi Keluar: " . ($trx->kendaraan->plat_nomor ?? 'N/A'), [
                'total_bayar' => $totalBayar,
                'durasi_menit' => $durasiMenit
            ]);

            $gateKeluar = Gate::where('tipe_gate', 'keluar')->first();

            $trx->update([
                'waktu_keluar'   => now(),
                'total_waktu'    => $durasiMenit,
                'total_bayar'    => $totalBayar,
                'status'         => 'selesai',
                'petugas_id'     => auth()->id(),
                'gate_keluar_id' => $gateKeluar?->id,
            ]);

            if ($trx->qrParkir) {
                $trx->qrParkir->update([
                    'status' => 'tersedia',
                    'aktif'  => true
                ]);
                Log::info("QR Code [{$trx->qrParkir->kode}] di-reset ke tersedia.");
            }

            $mqttPayload = [
                'id'      => uniqid('cmd_'),
                'command' => 'OPEN_GATE_EXIT',
                'payload' => [
                    'transaksi_id' => $trx->id,
                    'plat_nomor'   => $trx->kendaraan->plat_nomor ?? 'N/A',
                    'total_bayar'  => $totalBayar,
                    'gate_id'      => $gateKeluar?->id
                ],
                'time' => now()->toDateTimeString()
            ];

            $mqttSent = MqttService::publish("smartparking/univ123/commands", $mqttPayload);

            broadcast(new \App\Events\TransaksiSelesai($trx));

            if ($trx->qrParkir) {
                broadcast(new \App\Events\QRUpdated([
                    'kode'   => $trx->qrParkir->kode,
                    'status' => 'tersedia',
                    'time'   => now()->format('H:i:s')
                ]));
            }

            DB::commit();
            event(new \App\Events\TransaksiChanged());

            return response()->json([
                'success'     => true,
                'message'     => $mqttSent ? 'Kendaraan berhasil keluar.' : 'Data tersimpan, MQTT gagal.',
                'mqtt_status' => $mqttSent,
                'data'        => [
                    'plat_nomor'   => $trx->kendaraan->plat_nomor,
                    'total_bayar'  => (int) $totalBayar,
                    'waktu_masuk'  => $trx->waktu_masuk,
                    'waktu_keluar' => $trx->waktu_keluar->format('Y-m-d H:i:s'),
                    'total_waktu'  => $durasiMenit
                ],
                'settings'    => [
                    'app_name'      => Setting::where('key', 'app_name')->first()?->value ?? 'SMART PARKING',
                    'lokasi_parkir' => Setting::where('key', 'lokasi_parkir')->first()?->value ?? '-',
                    'alamat'        => Setting::where('key', 'alamat')->first()?->value ?? '-',
                    'kontak'        => Setting::where('key', 'kontak')->first()?->value ?? '-',
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("ERROR PROSES KELUAR: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses data keluar: ' . $e->getMessage()
            ], 500);
        }
    }
}
