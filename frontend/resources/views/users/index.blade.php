@extends('layouts.app')

@section('title', 'Manajemen Pengguna & Hak Akses')
@section('breadcrumb', 'Pengaturan / Manajemen Pengguna')
@section('page_title', 'Pengguna & Otorisasi Sistem HSSE')

@section('content')
<div class="space-y-6" x-data="{ 
    addModalOpen: false, 
    editModalOpen: false, 
    editUser: { id: '', name: '', email: '', role_id: '', phone: '', jabatan: '', is_active: true } 
}">

    <!-- Flash message -->
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border-l-4 border-[#008B5A] rounded-r-lg text-emerald-800 text-xs flex items-center justify-between">
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 bg-red-50 border-l-4 border-[#DC2626] rounded-r-lg text-red-800 text-xs flex items-center justify-between">
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Role Information Banner -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="p-4 bg-white border border-slate-200 rounded-xl shadow-xs">
            <div class="flex items-center space-x-2 text-xs font-bold text-slate-800 mb-1">
                <span class="w-2.5 h-2.5 rounded-full bg-purple-600"></span>
                <span>Super Admin</span>
            </div>
            <p class="text-[11px] text-slate-500">Akses penuh konfigurasi, manajemen akun, audit log & otorisasi.</p>
        </div>
        <div class="p-4 bg-white border border-slate-200 rounded-xl shadow-xs">
            <div class="flex items-center space-x-2 text-xs font-bold text-slate-800 mb-1">
                <span class="w-2.5 h-2.5 rounded-full bg-[#00529B]"></span>
                <span>Admin HSSE</span>
            </div>
            <p class="text-[11px] text-slate-500">Kelola master sarfas, checklist template, jadwal & review inspeksi.</p>
        </div>
        <div class="p-4 bg-white border border-slate-200 rounded-xl shadow-xs">
            <div class="flex items-center space-x-2 text-xs font-bold text-slate-800 mb-1">
                <span class="w-2.5 h-2.5 rounded-full bg-[#008B5A]"></span>
                <span>Inspektor Lapangan</span>
            </div>
            <p class="text-[11px] text-slate-500">Pelaksana checklist di lapangan, upload bukti foto, submit draft.</p>
        </div>
        <div class="p-4 bg-white border border-slate-200 rounded-xl shadow-xs">
            <div class="flex items-center space-x-2 text-xs font-bold text-slate-800 mb-1">
                <span class="w-2.5 h-2.5 rounded-full bg-[#F59E0B]"></span>
                <span>Approver / Verifikator</span>
            </div>
            <p class="text-[11px] text-slate-500">Verifikasi penutupan temuan, persetujuan dan sign-off hasil inspeksi.</p>
        </div>
    </div>

    <!-- Action Bar -->
    <div class="p-4 bg-white border border-slate-200 rounded-xl shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-sm font-bold text-slate-800">Daftar Akun Pengguna Terdaftar</h3>
            <p class="text-xs text-slate-500">Total {{ count($users) }} pengguna terdaftar dalam sistem Checklist Sarfas HSSE FT Maos</p>
        </div>

        @if(in_array(session('auth_user.role_slug'), ['super_admin', 'admin_hsse']))
            <button @click="addModalOpen = true" class="px-4 py-2 text-xs font-semibold text-white bg-[#D71920] hover:bg-[#b8141a] rounded-lg shadow-xs transition flex items-center shrink-0 justify-center">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                Tambah Pengguna Baru
            </button>
        @endif
    </div>

    <!-- Users Table -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-700 uppercase font-semibold text-[11px] tracking-wider">
                    <tr>
                        <th class="py-3 px-4">Pengguna</th>
                        <th class="py-3 px-4">Kontak / Email</th>
                        <th class="py-3 px-4">Jabatan</th>
                        <th class="py-3 px-4">Hak Akses (Role)</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-9 h-9 rounded-full bg-slate-200 text-slate-700 font-bold flex items-center justify-center shrink-0 uppercase">
                                        {{ substr($user['name'] ?? 'U', 0, 2) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900">{{ $user['name'] }}</div>
                                        <div class="text-[10px] text-slate-400">ID: #{{ $user['id'] }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-medium text-slate-700">{{ $user['email'] }}</div>
                                <div class="text-[10px] text-slate-400">{{ $user['phone'] ?? '-' }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-medium text-slate-700">
                                {{ $user['jabatan'] ?? 'Staff HSSE' }}
                            </td>
                            <td class="py-3.5 px-4">
                                @php
                                    $roleBadge = match($user['role_slug'] ?? '') {
                                        'super_admin' => 'bg-purple-100 text-purple-800 border-purple-200',
                                        'admin_hsse' => 'bg-blue-100 text-[#00529B] border-blue-200',
                                        'inspector' => 'bg-emerald-100 text-[#008B5A] border-emerald-200',
                                        'approver' => 'bg-amber-100 text-amber-800 border-amber-200',
                                        default => 'bg-slate-100 text-slate-700 border-slate-200'
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $roleBadge }}">
                                    {{ $user['role_name'] ?? $user['role_slug'] ?? 'User' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($user['is_active'] ?? true)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span> Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-500 border border-slate-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400 mr-1.5"></span> Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <button type="button" 
                                        @click="editUser = { 
                                            id: '{{ $user['id'] }}', 
                                            name: '{{ addslashes($user['name']) }}', 
                                            email: '{{ addslashes($user['email']) }}', 
                                            role_id: '{{ $user['role_id'] }}', 
                                            phone: '{{ addslashes($user['phone'] ?? '') }}', 
                                            jabatan: '{{ addslashes($user['jabatan'] ?? '') }}', 
                                            is_active: {{ ($user['is_active'] ?? true) ? 'true' : 'false' }} 
                                        }; editModalOpen = true"
                                        class="p-1.5 text-slate-500 hover:text-[#00529B] hover:bg-slate-100 rounded transition inline-flex items-center" 
                                        title="Ubah Pengguna">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    <span class="ml-1 text-[11px] font-medium">Edit</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">
                                Belum ada data pengguna yang terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL: TAMBAH PENGGUNA BARU -->
    <div x-show="addModalOpen" 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4" 
         x-cloak>
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-slate-100" @click.away="addModalOpen = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                <h3 class="text-sm font-bold text-slate-800">Registrasi Pengguna Baru</h3>
                <button @click="addModalOpen = false" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>

            <form action="{{ route('users.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap *</label>
                    <input type="text" name="name" required class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none" placeholder="cth: Ahmad Suherman">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Email Pertamina / Operasional *</label>
                        <input type="email" name="email" required class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none" placeholder="nama@pertamina.com">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Kata Sandi Awal *</label>
                        <input type="password" name="password" required minlength="6" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none" placeholder="Min. 6 karakter">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Hak Akses / Role *</label>
                        <select name="role_id" required class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none">
                            @foreach($roles as $role)
                                <option value="{{ $role['id'] }}">{{ $role['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Jabatan / Posisi</label>
                        <input type="text" name="jabatan" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none" placeholder="cth: Officer HSSE FT Maos">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Telepon / WhatsApp</label>
                    <input type="text" name="phone" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none" placeholder="0812xxxxxxxx">
                </div>

                <div class="flex items-center justify-end space-x-2 pt-4 border-t border-slate-100">
                    <button type="button" @click="addModalOpen = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition">Batal</button>
                    <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-[#D71920] hover:bg-[#b8141a] rounded-lg shadow-xs transition">Simpan Pengguna</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: EDIT PENGGUNA -->
    <div x-show="editModalOpen" 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4" 
         x-cloak>
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-slate-100" @click.away="editModalOpen = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                <h3 class="text-sm font-bold text-slate-800">Edit Data Pengguna</h3>
                <button @click="editModalOpen = false" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>

            <form :action="'{{ url('/users') }}/' + editUser.id" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap *</label>
                    <input type="text" name="name" x-model="editUser.name" required class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Email (Akun Login)</label>
                    <input type="email" x-model="editUser.email" disabled class="w-full px-3 py-2 text-xs border border-slate-200 bg-slate-100 text-slate-500 rounded-lg cursor-not-allowed">
                    <span class="text-[10px] text-slate-400">Email tidak dapat diubah setelah terdaftar.</span>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Hak Akses / Role *</label>
                        <select name="role_id" x-model="editUser.role_id" required class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none">
                            @foreach($roles as $role)
                                <option value="{{ $role['id'] }}">{{ $role['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Jabatan / Posisi</label>
                        <input type="text" name="jabatan" x-model="editUser.jabatan" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Telepon / WhatsApp</label>
                        <input type="text" name="phone" x-model="editUser.phone" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Reset Sandi (Opsional)</label>
                        <input type="password" name="password" minlength="6" placeholder="Kosongkan jika tetap" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none">
                    </div>
                </div>

                <div class="pt-2">
                    <label class="inline-flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" :checked="editUser.is_active" class="rounded border-slate-300 text-[#008B5A] focus:ring-[#008B5A]">
                        <span class="text-xs font-semibold text-slate-700">Akun Aktif (Dapat Login & Melakukan Operasi)</span>
                    </label>
                </div>

                <div class="flex items-center justify-end space-x-2 pt-4 border-t border-slate-100">
                    <button type="button" @click="editModalOpen = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition">Batal</button>
                    <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-[#00529B] hover:bg-[#003e75] rounded-lg shadow-xs transition">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
