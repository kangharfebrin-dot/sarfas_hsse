<?php

namespace App\Http\Controllers;

use App\Services\ExpressApiService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected ExpressApiService $api;

    public function __construct(ExpressApiService $api)
    {
        $this->api = $api;
    }

    public function index()
    {
        $data = $this->api->getUsers();
        $users = $data['users'] ?? [];
        $roles = $data['roles'] ?? [];

        return view('users.index', compact('users', 'roles'));
    }

    public function store(Request $request)
    {
        $payload = $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6',
            'role_id' => 'required|numeric',
            'phone' => 'nullable',
            'jabatan' => 'nullable',
        ]);

        $res = $this->api->createUser($payload);

        if ($res['success'] ?? false) {
            return redirect()->route('users.index')->with('success', 'Akun pengguna berhasil dibuat.');
        }

        return back()->withInput()->with('error', $res['message'] ?? 'Gagal membuat pengguna.');
    }

    public function update(Request $request, $id)
    {
        $payload = $request->validate([
            'name' => 'required',
            'role_id' => 'required|numeric',
            'phone' => 'nullable',
            'jabatan' => 'nullable',
            'password' => 'nullable|min:6',
            'is_active' => 'nullable',
        ]);

        $payload['is_active'] = $request->has('is_active');

        $res = $this->api->updateUser($id, $payload);

        if ($res['success'] ?? false) {
            return redirect()->route('users.index')->with('success', 'Data pengguna berhasil diperbarui.');
        }

        return back()->withInput()->with('error', $res['message'] ?? 'Gagal memperbarui pengguna.');
    }
}
