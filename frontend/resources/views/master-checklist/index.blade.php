@extends('layouts.app')

@section('title', 'Master Template Checklist')
@section('breadcrumb', 'Konfigurasi / Master Checklist')
@section('page_title', 'Master Standar Checklist Inspeksi')

@section('content')
<div class="space-y-6" x-data="{ createModalOpen: false }">

    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-sm font-bold text-slate-800">Template Standar Pemeriksaan Sarfas</h2>
            <p class="text-xs text-slate-500">Susun dan kelola daftar periksa berkala sesuai standar HSSE & NFPA tanpa mengubah kode program.</p>
        </div>
        @if(in_array(session('auth_user.role_slug'), ['super_admin', 'admin_hsse']))
            <button @click="createModalOpen = true" class="px-3.5 py-2 text-xs font-semibold text-white bg-[#D71920] hover:bg-[#b8141a] rounded-lg shadow-xs transition flex items-center shrink-0">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Buat Template Baru
            </button>
        @endif
    </div>

    <!-- Templates Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($templates as $tmpl)
            <div class="bg-white border border-slate-200 rounded-xl shadow-xs p-5 flex flex-col justify-between hover:border-slate-300 transition">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-mono font-bold text-[#00529B] bg-blue-50 px-2 py-0.5 rounded border border-blue-200">
                            {{ $tmpl['template_code'] }}
                        </span>
                        <span class="text-[10px] font-semibold text-slate-500 bg-slate-100 px-2 py-0.5 rounded">
                            v{{ $tmpl['version'] }} · {{ $tmpl['inspection_frequency'] }}
                        </span>
                    </div>

                    <h3 class="text-sm font-bold text-slate-900 leading-snug">
                        {{ $tmpl['title'] }}
                    </h3>

                    <p class="text-xs text-slate-500 line-clamp-2">
                        {{ $tmpl['description'] ?? 'Pemeriksaan standar operasional sarana dan fasilitas HSSE.' }}
                    </p>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-600">
                        <span>Kategori: <strong class="text-slate-800">{{ $tmpl['category_name'] }}</strong></span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 font-medium text-[11px]">
                            {{ $tmpl['total_items'] ?? 7 }} Item Pemeriksaan
                        </span>
                    </div>
                </div>

                <div class="mt-5 pt-3 border-t border-slate-100 flex items-center justify-between">
                    <span class="inline-flex items-center text-[10px] font-bold text-emerald-600">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span> AKTIF DIGUNAKAN
                    </span>
                    <a href="{{ route('master-checklist.show', $tmpl['id']) }}" class="px-3 py-1.5 text-xs font-semibold text-[#00529B] bg-blue-50 hover:bg-blue-100 rounded-lg transition">
                        Lihat Item &rarr;
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-3 p-12 text-center bg-white rounded-xl border border-slate-200 text-slate-400 text-xs">
                Belum ada template checklist yang terdaftar.
            </div>
        @endforelse
    </div>

    <!-- MODAL CREATE TEMPLATE -->
    <div x-show="createModalOpen" 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4" 
         x-cloak>
        <div @click.away="createModalOpen = false" class="bg-white rounded-xl shadow-xl border border-slate-200 max-w-lg w-full p-6">
            <div class="flex items-center justify-between pb-3 border-b border-slate-200 mb-4">
                <h3 class="text-sm font-bold text-slate-900">Buat Master Template Checklist Baru</h3>
                <button @click="createModalOpen = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>

            <form action="{{ route('master-checklist.store') }}" method="POST" class="space-y-3.5">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Kode Template *</label>
                        <input type="text" name="template_code" placeholder="Misal: TMPL-P3K-M" required
                               class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Frekuensi Inspeksi *</label>
                        <select name="inspection_frequency" required class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none">
                            <option value="HARIAN">Harian</option>
                            <option value="MINGGUAN">Mingguan</option>
                            <option value="BULANAN" selected>Bulanan</option>
                            <option value="TRIWULAN">Triwulan</option>
                            <option value="SEMESTER">Semester</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Judul Checklist *</label>
                    <input type="text" name="title" placeholder="Misal: Checklist Inspeksi Kotak & Fasilitas P3K" required
                           class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kategori Sarfas *</label>
                    <select name="category_id" required class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none">
                        <option value="">Pilih Kategori Terkait</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat['id'] }}">{{ $cat['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Deskripsi Standar</label>
                    <textarea name="description" rows="2" placeholder="Tujuan inspeksi dan acuan standar yang digunakan..."
                              class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none"></textarea>
                </div>

                <div class="flex justify-end space-x-2 pt-3 border-t border-slate-200">
                    <button type="button" @click="createModalOpen = false" class="px-3 py-2 text-xs font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-[#D71920] hover:bg-[#b8141a] rounded-lg transition">
                        Buat Template
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
