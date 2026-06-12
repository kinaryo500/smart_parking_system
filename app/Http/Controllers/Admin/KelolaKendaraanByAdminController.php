<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kendaraan;
use Illuminate\Http\Request;

class KelolaKendaraanByAdminController extends Controller
{
    public function store(Request $request)
    {
        // Validasi input dengan custom error messages berbahasa Indonesia
        $validated = $request->validate([
            'user_id'    => 'required|exists:users,id',
            'jenis'      => 'required|in:motor,mobil',
            'plat_nomor' => 'required|string|max:255|unique:kendaraans,plat_nomor',
            'merk'       => 'required|string|max:255',
            'warna'      => 'required|string|max:255',
        ], [
            'user_id.required'    => 'Data user tidak ditemukan.',
            'user_id.exists'      => 'User yang dipilih tidak terdaftar dalam sistem.',
            'jenis.required'      => 'Jenis kendaraan wajib dipilih.',
            'jenis.in'            => 'Pilih jenis kendaraan yang valid (Motor atau Mobil).',
            'plat_nomor.required' => 'Plat nomor tidak boleh kosong.',
            'plat_nomor.unique'   => 'Plat nomor ini sudah terdaftar sebelumnya.',
            'merk.required'       => 'Merk / model kendaraan wajib diisi.',
            'warna.required'      => 'Warna kendaraan wajib diisi.',
        ]);

        try {
            Kendaraan::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Kendaraan berhasil ditambahkan.'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data kendaraan.'
            ], 500);
        }
    }

    /**
     * Menghapus data kendaraan.
     */
    public function destroy($id)
    {
        try {
            $kendaraan = Kendaraan::findOrFail($id);
            $kendaraan->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data kendaraan berhasil dihapus.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data kendaraan.'
            ], 500);
        }
    }
}