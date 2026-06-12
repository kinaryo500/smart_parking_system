<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login()
    {
        return view('auth.login');
    }

    public function register()
    {
        return view('auth.register');
    }

    public function authenticate(Request $request)
    {
        $messages = [
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ];

        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ], $messages);

        if (!Auth::attempt($credentials)) {
            return back()
                ->withInput()
                ->with('error', 'Email atau password yang Anda masukkan salah.');
        }

        $request->session()->regenerate();

        $user = Auth::user();

        if (!$user->is_active) {

            Auth::logout();

            return redirect()
                ->route('login')
                ->with('error', 'Akun Anda telah dinonaktifkan.');
        }

        return match ($user->role) {

            'admin' => redirect()
                ->route('admin.dashboard')
                ->with('success', 'Selamat datang Admin.'),

            'petugas' => redirect()
                ->route('petugas.dashboard')
                ->with('success', 'Selamat datang Petugas.'),

            'pegawai' => redirect()
                ->route('pegawai.dashboard')
                ->with('success', 'Selamat datang Pegawai.'),

            'pasien' => redirect()
                ->route('pasien.dashboard')
                ->with('success', 'Selamat datang Pasien.'),

            'user' => redirect()
                ->route('user.dashboard')
                ->with('success', 'Selamat datang di Smart Parking System.'),

            default => abort(403, 'Role tidak dikenali.'),
        };
    }

    public function store(Request $request)
    {
        $messages = [
            'name.required'        => 'Nama lengkap wajib diisi.',
            'name.string'          => 'Nama harus berupa teks.',
            'name.max'             => 'Nama terlalu panjang.',

            'email.required'       => 'Email wajib diisi.',
            'email.email'          => 'Format email tidak valid.',
            'email.unique'         => 'Email sudah digunakan.',

            'no_hp.required'       => 'Nomor HP wajib diisi.',
            'no_hp.max'            => 'Nomor HP terlalu panjang.',

            'password.required'    => 'Password wajib diisi.',
            'password.min'         => 'Password minimal 6 karakter.',
            'password.confirmed'   => 'Konfirmasi password tidak cocok.',
        ];

        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'no_hp'    => 'required|string|max:20',
            'password' => 'required|min:6|confirmed',
        ], $messages);

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'no_hp'     => $request->no_hp,
            'role'      => 'user',
            'is_active' => true,
            'password'  => Hash::make($request->password),
        ]);

        Auth::login($user);

        return redirect()
            ->route('user.dashboard')
            ->with(
                'success',
                'Registrasi berhasil! Selamat datang di Smart Parking System.'
            );
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('success', 'Anda telah berhasil logout.');
    }
}