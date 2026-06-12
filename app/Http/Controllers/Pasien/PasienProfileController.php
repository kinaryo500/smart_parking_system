<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class PasienProfileController extends Controller
{
    /**
     * Menampilkan halaman profil pasien.
     */
    public function index()
    {
        $user = Auth::user();
        return view('pasien.profile.index', compact('user'));
    }

    /**
     * Memperbarui data profil pasien (Nama, Email, No HP).
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

        // Diarahkan kembali ke route profil pasien
        return redirect()->route('pasien.profile')->with('success', 'Profil pasien berhasil diperbarui!');
    }

    /**
     * Memperbarui kata sandi akun pasien.
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


        return redirect()->route('pasien.profile')->with('success', 'Password Anda berhasil diperbarui!');
    }
}
