<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Exception;

class AdminKelolaPasienController extends Controller
{
    public function index()
    {
        return view('admin.kelola-pasien.index');
    }

    public function data(Request $request)
    {
        $query = User::where('role', 'pasien');

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('no_hp', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'aktif') {
                $query->where('is_active', true);
            }

            if ($request->status === 'nonaktif') {
                $query->where('is_active', false);
            }
        }

        $pasien = $query
            ->latest()
            ->get([
                'id',
                'name',
                'email',
                'no_hp',
                'is_active'
            ]);

        return response()->json([
            'success' => true,
            'data' => $pasien
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name'     => 'required|string|max:255',
                'email'    => 'required|email|unique:users,email',
                'no_hp'    => 'nullable|numeric|digits_between:10,15',
                'password' => 'required|min:6',
            ], [
                'name.required'        => 'Nama lengkap wajib diisi.',
                'email.required'       => 'Email wajib diisi.',
                'email.email'          => 'Format email tidak valid.',
                'email.unique'         => 'Email sudah digunakan.',
                'no_hp.numeric'        => 'Nomor HP harus berupa angka.',
                'no_hp.digits_between' => 'Nomor HP harus 10-15 digit.',
                'password.required'    => 'Password wajib diisi.',
                'password.min'         => 'Password minimal 6 karakter.',
            ]);

            User::create([
                'name'      => $validated['name'],
                'email'     => $validated['email'],
                'no_hp'     => $validated['no_hp'] ?? null,
                'password'  => Hash::make($validated['password']),
                'role'      => 'pasien',
                'is_active' => $request->is_active ?? true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pasien berhasil ditambahkan.'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            Log::error('Gagal tambah pasien', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem.'
            ], 500);
        }
    }

    public function show($id)
    {

        $pasien = User::where('role', 'pasien')
            ->with(['kendaraans.parkirTransaksis'])
            ->findOrFail($id);

        $totalKendaraan = $pasien->kendaraans->count();

        $kendaraanList = $pasien->kendaraans->map(function ($kendaraan) {
            return [
                'id'           => $kendaraan->id,
                'plat_nomor'   => $kendaraan->plat_nomor ?? '-',
                'merk'         => $kendaraan->merk ?? '-',
                'total_parkir' => $kendaraan->parkirTransaksis ? $kendaraan->parkirTransaksis->count() : 0
            ];
        });

        // Total keseluruhan sesi parkir dari semua kendaraan pasien ini
        $totalParkir = $kendaraanList->sum('total_parkir');

        return view('admin.kelola-pasien.show', compact(
            'pasien',
            'totalKendaraan',
            'totalParkir',
            'kendaraanList'
        ));
    }

    public function edit($id)
    {
        $pasien = User::where('role', 'pasien')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $pasien
        ]);
    }

    public function update(Request $request, $id)
    {
        try {
            $pasien = User::where('role', 'pasien')
                ->findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => [
                    'required',
                    'email',
                    Rule::unique('users')->ignore($pasien->id)
                ],
                'no_hp' => 'nullable|numeric|digits_between:10,15',
                'password' => 'nullable|min:6',
            ], [
                'name.required'  => 'Nama wajib diisi.',
                'email.required' => 'Email wajib diisi.',
                'email.unique'   => 'Email sudah digunakan.',
                'password.min'   => 'Password minimal 6 karakter.',
            ]);

            $data = [
                'name'      => $validated['name'],
                'email'     => $validated['email'],
                'no_hp'     => $validated['no_hp'] ?? null,
                'is_active' => $request->is_active ?? true,
            ];

            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $pasien->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Data pasien berhasil diperbarui.'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            Log::error('Gagal update pasien', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data pasien.'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $pasien = User::where('role', 'pasien')
                ->findOrFail($id);

            if ($pasien->id == auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat menghapus akun sendiri.'
                ], 400);
            }

            $pasien->delete();

            return response()->json([
                'success' => true,
                'message' => 'Pasien berhasil dihapus.'
            ]);
        } catch (Exception $e) {
            Log::error('Gagal hapus pasien', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem.'
            ], 500);
        }
    }

    public function toggleStatus($id)
    {
        try {
            $pasien = User::where('role', 'pasien')
                ->findOrFail($id);

            $pasien->update([
                'is_active' => !$pasien->is_active
            ]);

            return response()->json([
                'success' => true,
                'message' => $pasien->is_active
                    ? 'Pasien berhasil diaktifkan.'
                    : 'Pasien berhasil dinonaktifkan.'
            ]);
        } catch (Exception $e) {
            Log::error('Gagal ubah status pasien', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah status pasien.'
            ], 500);
        }
    }
}
