<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class PegawaiProfileController extends Controller
{
    /**
     * Menampilkan halaman profil pegawai.
     */
    public function index()
    {
        $user = Auth::user();
        return view('pegawai.profile.index', compact('user'));
    }

    /**
     * Memperbarui data profil pegawai (Nama, Email, No HP).
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'no_hp' => [
                'required',
                'string',
                'max:20',
                Rule::unique('users')->ignore($user->id),
            ],
        ], [
            'name.required' => 'Nama wajib diisi',
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Email tidak valid',
            'email.unique' => 'Email sudah digunakan',
            'no_hp.required' => 'Nomor HP wajib diisi',
            'no_hp.unique' => 'Nomor HP sudah digunakan',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->no_hp = $request->no_hp;
        $user->save();

        // Diarahkan kembali ke route profil pegawai
        return redirect()->route('pegawai.profile')->with('success', 'Profil pegawai berhasil diperbarui!');
    }

    /**
     * Memperbarui kata sandi akun pegawai.
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => [
                'required',
                function ($attribute, $value, $fail) use ($user) {
                    if (!Hash::check($value, $user->password)) {
                        $fail('Password saat ini salah.');
                    }
                },
            ],
            'password' => 'required|string|min:8|confirmed|different:current_password',
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal harus 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.different' => 'Password baru tidak boleh sama dengan password saat ini.',
        ]);

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        // Diarahkan kembali ke route profil pegawai
        return redirect()->route('pegawai.profile')->with('success', 'Password Anda berhasil diperbarui!');
    }
}