<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return Auth::user()->isOwner() ? redirect()->route('dashboard') : redirect()->route('pos.index');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = Str::transliterate(Str::lower($request->input('email')).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'email' => "Terlalu banyak percobaan login gagal. Silakan coba lagi dalam {$seconds} detik.",
            ])->onlyInput('email');
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            RateLimiter::clear($throttleKey);

            $user = Auth::user();
            if (! $user->is_active) {
                Auth::logout();
                return back()->withErrors(['email' => 'Akun Anda dinonaktifkan. Silakan hubungi pemilik.']);
            }

            $user->last_login_at = Carbon::now('Asia/Jakarta');
            $user->save();

            // Cashiers redirect straight to POS; Owners redirect to Dashboard
            if ($user->isCashier()) {
                return redirect()->intended(route('pos.index'));
            }
            return redirect()->intended(route('dashboard'));
        }

        RateLimiter::hit($throttleKey, 60);

        return back()->withErrors([
            'email' => 'Email atau kata sandi yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    public function demoLogin(Request $request)
    {
        $role = $request->input('role', 'owner');

        $user = match($role) {
            'cashier' => User::where('role', UserRole::CASHIER)->where('is_active', true)->first(),
            default => User::where('role', UserRole::OWNER)->where('is_active', true)->first(),
        };

        if (! $user) {
            return redirect()->route('login')->withErrors(['email' => 'Akun demo untuk peran ini belum tersedia.']);
        }

        Auth::login($user);
        $request->session()->regenerate();

        $user->last_login_at = Carbon::now('Asia/Jakarta');
        $user->save();

        if ($user->isCashier()) {
            return redirect()->route('pos.index')->with('success', "Selamat datang di Demo Kasir! Anda login sebagai {$user->name}.");
        }

        return redirect()->route('dashboard')->with('success', "Selamat datang di Demo Pemilik! Anda login sebagai {$user->name}.");
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar.');
    }

    public function verifyPin(Request $request)
    {
        $request->validate(['pin' => ['required', 'string']]);

        $user = Auth::user();
        if (! $user || ! $user->verifyPin($request->pin)) {
            return response()->json(['success' => false, 'message' => 'PIN kasir tidak cocok.'], 422);
        }

        return response()->json(['success' => true, 'message' => 'PIN terverifikasi.']);
    }
}
