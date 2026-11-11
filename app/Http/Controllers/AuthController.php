<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $demoUsers = User::with('role')->where('is_active', true)->get();
        return view('auth.login', compact('demoUsers'));
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();

            if (!$user->is_active) {
                Auth::logout();
                return back()->withErrors(['email' => 'Akun Anda telah dinonaktifkan oleh Administrator.']);
            }

            return redirect()->intended(route('dashboard'))
                ->with('success', "Selamat datang kembali, {$user->name} ({$user->role->name})!");
        }

        return back()->withErrors([
            'email' => 'Kombinasi email dan password yang Anda masukkan tidak cocok.',
        ])->onlyInput('email');
    }

    /**
     * Fitur 1-Klik Cepat Ganti Role untuk Mempermudah Pengujian Tim & Evaluasi Sistem
     */
    public function quickLogin(string $roleCode): RedirectResponse
    {
        $role = Role::where('code', strtoupper($roleCode))->firstOrFail();
        $user = User::where('role_id', $role->id)->where('is_active', true)->firstOrFail();

        Auth::login($user);
        request()->session()->regenerate();

        return redirect()->route('dashboard')
            ->with('success', "Beralih sukses ke akun {$user->name} ({$role->name})!");
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('info', 'Anda telah berhasil keluar dari sistem.');
    }
}
