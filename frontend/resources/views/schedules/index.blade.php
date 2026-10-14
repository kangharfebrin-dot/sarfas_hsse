@extends('layouts.app')

@section('title', 'Jadwal Inspeksi Berkala')
@section('breadcrumb', 'Penjadwalan / Jadwal Inspeksi')
@section('page_title', 'Perencanaan & Kalender Inspeksi Sarfas')

@section('content')
<div class="space-y-6" x-data="{ addScheduleModal: false }">

    <!-- Header Actions & Filters -->
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <form method="GET" action="{{ route('schedules.index') }}" class="flex-1 grid grid-cols-1 sm:grid-cols-3 gap-2.5">
            <div>
                <select name="status" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none">
                    <option value="">-- Semua Status Jadwal --</option>
                    <option value="TERJADWAL" {{ ($filters['status'] ?? '') == 'TERJADWAL' ? 'selected' : '' }}>Terjadwal</option>
                    <option value="TERLAMBAT" {{ ($filters['status'] ?? '') == 'TERLAMBAT' ? 'selected' : '' }}>Terlambat (Overdue)</option>
                    <option value="SELESAI" {{ ($filters['status'] ?? '') == 'SELESAI' ? 'selected' : '' }}>Selesai</option>
                </select>
            </div>
            <div>
                <select name="sarfas_id" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none">
                    <option value="">-- Semua Sarfas --</option>
                    @foreach($sarfasList as $s)
                        <option value="{{ $s['id'] }}" {{ ($filters['sarfas_id'] ?? '') == $s['id'] ? 'selected' : '' }}>
                            {{ $s['asset_code'] }} - {{ $s['name'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex space-x-2">
                <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-[#00529B] hover:bg-[#003e75] rounded-lg transition shrink-0">
                    Filter Jadwal
                </button>
            </div>
        </form>

        @if(in_array(session('auth_user.role_slug'), ['super_admin', 'admin_hsse']))
            <button @click="addScheduleModal = true" class="px-4 py-2 text-xs font-semibold text-white bg-[#D71920] hover:bg-[#b8141a] rounded-lg shadow-xs transition flex items-center shrink-0 justify-center">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Buat Jadwal Inspeksi
            </button>
        @endif
    </div>

    <!-- Schedules Table -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-200 bg-slate-50/50 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800">Daftar Agenda Pemeriksaan Sarfas</h3>
            <span class="text-xs text-slate-500">{{ count($schedules) }} Agenda Terdaftar</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-100/70 text-slate-700 font-bold uppercase text-[10px]">
                        <th class="p-3.5">Tanggal Pelaksanaan</th>
                        <th class="p-3.5">Nama Kegiatan Agenda</th>
                        <th class="p-3.5">Sarfas Terkait</th>
                        <th class="p-3.5">Template Checklist</th>
                        <th class="p-3.5">Petugas Inspektor (PIC)</th>
                        <th class="p-3.5">Frekuensi</th>
                        <th class="p-3.5">Status</th>
                        <th class="p-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($schedules as $sched)
                        @php
                            $isOverdue = $sched['status'] === 'TERLAMBAT' || ($sched['status'] === 'TERJADWAL' && strtotime($sched['scheduled_date']) < time());
                        @endphp
                        <tr class="hover:bg-slate-50 transition {{ $isOverdue ? 'bg-red-50/20' : '' }}">
                            <td class="p-3.5 font-medium">
                                <div class="font-bold {{ $isOverdue ? 'text-red-600' : 'text-slate-900' }}">
                                    {{ date('d M Y', strtotime($sched['scheduled_date'])) }}
                                </div>
                                @if($isOverdue)
                                    <span class="text-[9px] font-bold text-red-600 uppercase">Terlambat</span>
                                @endif
                            </td>
                            <td class="p-3.5">
                                <div class="font-bold text-slate-900">{{ $sched['title'] }}</div>
                                @if($sched['notes'])
                                    <div class="text-[11px] text-slate-500 mt-0.5">{{ $sched['notes'] }}</div>
                                @endif
                            </td>
                            <td class="p-3.5">
                                <div class="font-semibold text-slate-800">{{ $sched['sarfas_name'] }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $sched['asset_code'] }} · {{ $sched['location_name'] }}</div>
                            </td>
                            <td class="p-3.5 text-slate-600">
                                {{ $sched['template_title'] }}
                            </td>
                            <td class="p-3.5 font-medium text-slate-800">
                                {{ $sched['assigned_user_name'] }}
                            </td>
                            <td class="p-3.5">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600">
                                    {{ $sched['frequency'] }}
                                </span>
                            </td>
                            <td class="p-3.5">
                                @if($sched['status'] === 'SELESAI')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        SELESAI
                                    </span>
                                @elseif($isOverdue)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-800">
                                        TERLAMBAT
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800">
                                        TERJADWAL
                                    </span>
                                @endif
                            </td>
                            <td class="p-3.5 text-right">
                                @if($sched['status'] !== 'SELESAI')
                                    <a href="{{ route('inspections.create', ['sarfas_id' => $sched['sarfas_id']]) }}" class="inline-flex items-center px-2.5 py-1 text-xs font-semibold text-white bg-[#008B5A] hover:bg-[#007048] rounded transition">
                                        Eksekusi Sekarang
                                    </a>
                                @else
                                    <span class="text-slate-400 text-xs">Selesai</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-8 text-center text-slate-400">
                                Tidak ada jadwal inspeksi yang sesuai dengan kriteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL CREATE SCHEDULE -->
    <div x-show="addScheduleModal" 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4" 
         x-cloak>
        <div @click.away="addScheduleModal = false" class="bg-white rounded-xl shadow-xl border border-slate-200 max-w-lg w-full p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                <h3 class="text-sm font-bold text-slate-900">Jadwalkan Inspeksi Sarfas Baru</h3>
                <button @click="addScheduleModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>

            <form action="{{ route('schedules.store') }}" method="POST" class="space-y-3.5">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Agenda Kegiatan *</label>
                    <input type="text" name="title" placeholder="Misal: Uji Kesiapan Pompa Pemadam Kebakaran" required
                           class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Sarfas yang Diperiksa *</label>
                        <select name="sarfas_id" required class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none bg-white">
                            <option value="">Pilih Sarfas</option>
                            @foreach($sarfasList as $s)
                                <option value="{{ $s['id'] }}">{{ $s['asset_code'] }} - {{ $s['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Template Checklist *</label>
                        <select name="template_id" required class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none bg-white">
                            <option value="">Pilih Template</option>
                            @foreach($templates as $t)
                                <option value="{{ $t['id'] }}">{{ $t['title'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Petugas Pelaksana (PIC) *</label>
                        <select name="assigned_user_id" required class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none bg-white">
                            <option value="">Pilih Petugas</option>
                            @foreach($users as $u)
                                <option value="{{ $u['id'] }}">{{ $u['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Frekuensi *</label>
                        <select name="frequency" required class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none bg-white">
                            <option value="HARIAN">Harian</option>
                            <option value="MINGGUAN">Mingguan</option>
                            <option value="BULANAN" selected>Bulanan</option>
                            <option value="TRIWULAN">Triwulan</option>
                            <option value="SEMESTER">Semester</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Pelaksanaan *</label>
                    <input type="date" name="scheduled_date" value="{{ date('Y-m-d') }}" required
                           class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Tambahan</label>
                    <textarea name="notes" rows="2" placeholder="Instruksi khusus bagi petugas inspektor..."
                              class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none"></textarea>
                </div>

                <div class="flex justify-end space-x-2 pt-3 border-t border-slate-200">
                    <button type="button" @click="addScheduleModal = false" class="px-3 py-2 text-xs font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-[#D71920] hover:bg-[#b8141a] rounded-lg transition">
                        Simpan Jadwal
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
