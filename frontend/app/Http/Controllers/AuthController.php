<?php

namespace App\Http\Controllers;

use App\Services\ExpressApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    protected ExpressApiService $api;

    public function __construct(ExpressApiService $api)
    {
        $this->api = $api;
    }

    public function showLogin()
    {
        if (Session::has('auth_user') && Session::has('jwt_token')) {
            return redirect()->route('dashboard');
        }

        $demoAccounts = $this->api->getDemoAccounts();
        return view('auth.login', compact('demoAccounts'));
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $result = $this->api->login($request->input('email'), $request->input('password'));

        if ($result['success']) {
            return redirect()->intended(route('dashboard'))->with('success', 'Selamat datang di Sistem Checklist Sarfas HSSE FT Maos.');
        }

        return back()->withInput()->with('error', $result['message']);
    }

    public function quickLogin(Request $request)
    {
        $email = $request->input('email', 'superadmin@pertamina.demo');
        $password = $request->input('password', 'password123');

        $result = $this->api->login($email, $password);

        if ($result['success']) {
            return redirect()->route('dashboard')->with('success', 'Berhasil masuk menggunakan akun demo.');
        }

        return redirect()->route('login')->with('error', $result['message']);
    }

    public function logout()
    {
        $this->api->logout();
        return redirect()->route('login')->with('info', 'Anda telah keluar dari aplikasi.');
    }
}
