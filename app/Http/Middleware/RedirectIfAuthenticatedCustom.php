<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticatedCustom
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {

            $user = Auth::user();

            if ($user->role === 'admin') {
                return redirect()->route('admin.dashboard');
            }

            if ($user->role === 'petugas') {
                return redirect()->route('petugas.dashboard');
            }

            if ($user->role === 'pegawai') {
                return redirect()->route('pegawai.dashboard');
            }

            if ($user->role === 'pasien') {
                return redirect()->route('pasien.dashboard');
            }

            if ($user->role === 'user') {
                return redirect()->route('user.dashboard');
            }
        }

        return $next($request);
    }
}