<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('pages.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');

        // Tentukan apakah input adalah email atau name
        $loginField = filter_var($credentials['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'name';
        
        // Buat array credentials dengan field yang sesuai
        $authCredentials = [
            $loginField => $credentials['login'],
            'password' => $credentials['password'],
        ];

        if (Auth::attempt($authCredentials, $remember)) {
            $request->session()->regenerate();

            // Tambahkan notifikasi selamat datang
            $user = Auth::user();
            session()->flash('success', "Selamat datang Kak {$user->name}! Selamat Bekerja");

            return redirect()->route('dashboard');
        }

        return back()->withErrors([
            'login' => 'Email/Nama atau password salah.',
        ])->onlyInput('login');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
