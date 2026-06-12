<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kendaraan;
use App\Models\ParkirTransaksi;
use App\Models\Tarif;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PasienKendaraanController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $kendaraan = Kendaraan::where('user_id', $user->id)->get();

        return view('pasien.kendaraan.index', compact('kendaraan', 'user'));
    }

    public function detail($id)
    {
        $user = Auth::user();

        $kendaraan = Kendaraan::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $transaksiRiwayat = ParkirTransaksi::where('kendaraan_id', $kendaraan->id)
            ->where('status', 'selesai')
            ->whereNotNull('waktu_keluar')
            ->orderBy('waktu_masuk', 'desc')
            ->get();

        // Sesuaikan kalkulasi total akumulasi pengeluaran parkir pasien
        $totalSemua = 0; 

        $parkirAktif = ParkirTransaksi::where('kendaraan_id', $kendaraan->id)
            ->where('status', 'aktif')
            ->whereNull('waktu_keluar')
            ->get()
            ->map(function ($t) {
                $waktuSekarang = now();
                $menit = Carbon::parse($t->waktu_masuk)->diffInMinutes($waktuSekarang);

                $t->estimasi_menit = $menit;
                $t->estimasi_bayar = 0; // Dapat disesuaikan dengan tarif/durasi pasien

                return $t;
            });

        return view('pasien.kendaraan.detail', compact(
            'user',
            'kendaraan',
            'transaksiRiwayat',
            'parkirAktif',
            'totalSemua'
        ));
    }

    public function detailHistory($id)
    {
        $transaksi = ParkirTransaksi::with([
            'kendaraan',
            'qrParkir'
        ])
            ->where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if ($transaksi->status === 'aktif') {
            $menit = Carbon::parse($transaksi->waktu_masuk)->diffInMinutes(now());

            $transaksi->estimasi_durasi = $menit;
            $transaksi->estimasi_biaya = 0; // Sesuaikan tarif pasien jika ada
        }

        return view('pasien.kendaraan.detailHistory', compact('transaksi'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'jenis' => 'required|in:motor,mobil',
                'plat_nomor' => 'required|string|max:20|unique:kendaraans,plat_nomor',
                'merk' => 'required|string|max:50',
                'warna' => 'required|string|max:30',
            ], [
                'jenis.required' => 'Jenis kendaraan wajib dipilih',
                'jenis.in' => 'Jenis kendaraan tidak valid',
                'plat_nomor.required' => 'Plat nomor wajib diisi',
                'plat_nomor.unique' => 'Plat nomor sudah terdaftar',
                'plat_nomor.max' => 'Plat nomor terlalu panjang',
                'merk.required' => 'Merk kendaraan wajib diisi',
                'merk.max' => 'Merk terlalu panjang',
                'warna.required' => 'Warna kendaraan wajib diisi',
                'warna.max' => 'Warna terlalu panjang',
            ]);

            $user = Auth::user();
            $plat = strtoupper(preg_replace('/\s+/', '', $validated['plat_nomor']));

            Kendaraan::create([
                'user_id' => $user->id,
                'jenis' => strtolower($validated['jenis']),
                'plat_nomor' => $plat,
                'merk' => ucfirst($validated['merk']),
                'warna' => ucfirst($validated['warna']),
            ]);

            return redirect()->back()->with('success', 'Kendaraan pasien berhasil ditambahkan');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput()
                ->with('error', 'Data tidak valid, silakan periksa kembali');
        } catch (\Exception $e) {
            Log::error('Pasien gagal tambah kendaraan', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan pada sistem');
        }
    }

    public function storeToParkir(Request $request)
    {
        try {
            $user = auth()->user();

            if ($request->kendaraan_id) {
                $kendaraan = Kendaraan::where('id', $request->kendaraan_id)
                    ->where('user_id', $user->id)
                    ->firstOrFail();
            } else {
                $validated = $request->validate([
                    'jenis' => 'required|in:motor,mobil',
                    'plat_nomor' => 'required|string|max:20',
                    'merk' => 'required|string|max:50',
                    'warna' => 'required|string|max:30',
                ]);

                $plat = strtoupper(preg_replace('/\s+/', '', $validated['plat_nomor']));
                $kendaraan = Kendaraan::where('plat_nomor', $plat)->first();

                if (!$kendaraan) {
                    $kendaraan = Kendaraan::create([
                        'user_id' => $user->id,
                        'plat_nomor' => $plat,
                        'jenis' => strtolower($validated['jenis']),
                        'merk' => ucfirst($validated['merk']),
                        'warna' => ucfirst($validated['warna']),
                    ]);
                } else if ($kendaraan->user_id !== $user->id) {
                    if ($request->ajax()) {
                        return response()->json(['message' => 'Plat nomor ini sudah terdaftar dengan akun lain.'], 422);
                    }
                    return redirect()->back()->with('error', 'Plat nomor ini sudah terdaftar dengan akun lain.');
                }
            }

            $sedangParkir = ParkirTransaksi::where('kendaraan_id', $kendaraan->id)
                ->where('status', 'aktif')
                ->exists();

            if ($sedangParkir) {
                $msg = 'Kendaraan ' . $kendaraan->plat_nomor . ' sedang dalam sesi parkir aktif.';
                if ($request->ajax()) {
                    return response()->json(['message' => $msg], 422);
                }
                return redirect()->back()->with('error', $msg);
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Sesi parkir pasien siap dimulai',
                    'redirect' => route('pasien.parkir.scan', $kendaraan->id)
                ]);
            }

            return redirect()->route('pasien.parkir.scan', $kendaraan->id);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'message' => 'Data tidak valid',
                    'errors' => $e->validator->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            Log::error('Pasien gagal proses parkir', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            if ($request->ajax()) {
                return response()->json(['message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem');
        }
    }

    public function getStatusApi($id)
    {
        $transaksi = ParkirTransaksi::where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

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
            'total_bayar_formatted' => "Rp 0" // Sesuaikan tarif
        ]);
    }

    public function update(Request $request, $id)
    {
        try {
            $user = Auth::user();

            $kendaraan = Kendaraan::where('id', $id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            $validated = $request->validate([
                'jenis' => 'required|in:motor,mobil',
                'plat_nomor' => 'required|string|max:20|unique:kendaraans,plat_nomor,' . $kendaraan->id,
                'merk' => 'required|string|max:50',
                'warna' => 'required|string|max:30',
            ], [
                'jenis.required' => 'Jenis kendaraan wajib dipilih',
                'jenis.in' => 'Jenis kendaraan tidak valid',
                'plat_nomor.required' => 'Plat nomor wajib diisi',
                'plat_nomor.unique' => 'Plat nomor sudah digunakan',
                'plat_nomor.max' => 'Plat nomor terlalu panjang',
                'merk.required' => 'Merk kendaraan wajib diisi',
                'merk.max' => 'Merk terlalu panjang',
                'warna.required' => 'Warna kendaraan wajib diisi',
                'warna.max' => 'Warna terlalu panjang',
            ]);

            $plat = strtoupper(preg_replace('/\s+/', '', $validated['plat_nomor']));

            $kendaraan->update([
                'jenis' => strtolower($validated['jenis']),
                'plat_nomor' => $plat,
                'merk' => ucfirst($validated['merk']),
                'warna' => ucfirst($validated['warna']),
            ]);

            return redirect()->back()->with('success', 'Data kendaraan pasien berhasil diperbarui');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput()
                ->with('error', 'Data tidak valid, silakan periksa kembali');
        } catch (\Exception $e) {
            Log::error('Pasien gagal update kendaraan', [
                'user_id' => Auth::id(),
                'kendaraan_id' => $id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat memperbarui data');
        }
    }
}