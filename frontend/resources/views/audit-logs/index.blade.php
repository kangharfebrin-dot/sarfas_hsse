@extends('layouts.app')

@section('title', 'Riwayat Aktivitas & Jejak Audit')
@section('breadcrumb', 'Pengaturan / Riwayat Aktivitas')
@section('page_title', 'Jejak Audit Sistem HSSE FT Maos')

@section('content')
<div class="space-y-6" x-data="{ 
    selectedLog: null, 
    detailModalOpen: false 
}">

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('audit-logs.index') }}" class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex-1 grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-500 mb-1">Filter Modul</label>
                    <select name="module" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none">
                        <option value="">-- Semua Modul --</option>
                        <option value="AUTH" {{ ($filters['module'] ?? '') == 'AUTH' ? 'selected' : '' }}>Autentikasi (AUTH)</option>
                        <option value="INSPECTIONS" {{ ($filters['module'] ?? '') == 'INSPECTIONS' ? 'selected' : '' }}>Inspeksi (INSPECTIONS)</option>
                        <option value="FINDINGS" {{ ($filters['module'] ?? '') == 'FINDINGS' ? 'selected' : '' }}>Temuan (FINDINGS)</option>
                        <option value="SARFAS" {{ ($filters['module'] ?? '') == 'SARFAS' ? 'selected' : '' }}>Sarfas (SARFAS)</option>
                        <option value="TEMPLATES" {{ ($filters['module'] ?? '') == 'TEMPLATES' ? 'selected' : '' }}>Master Checklist (TEMPLATES)</option>
                        <option value="SCHEDULES" {{ ($filters['module'] ?? '') == 'SCHEDULES' ? 'selected' : '' }}>Jadwal (SCHEDULES)</option>
                        <option value="USERS" {{ ($filters['module'] ?? '') == 'USERS' ? 'selected' : '' }}>Manajemen Pengguna (USERS)</option>
                    </select>
                </div>

                <div class="sm:col-span-2 flex items-end space-x-2">
                    <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-[#00529B] hover:bg-[#003e75] rounded-lg transition">
                        Terapkan Filter
                    </button>
                    @if(!empty($filters['module']) || !empty($filters['user_id']))
                        <a href="{{ route('audit-logs.index') }}" class="px-3.5 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition">
                            Reset
                        </a>
                    @endif
                </div>
            </div>

            <div class="text-xs text-slate-500 text-right">
                Menampilkan <span class="font-bold text-slate-800">{{ count($logs) }}</span> catatan aktivitas terbaru
            </div>
        </form>
    </div>

    <!-- Audit Logs Table -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-700 uppercase font-semibold text-[11px] tracking-wider">
                    <tr>
                        <th class="py-3 px-4 w-40">Waktu & Tanggal</th>
                        <th class="py-3 px-4">Pengguna</th>
                        <th class="py-3 px-4">Aksi</th>
                        <th class="py-3 px-4">Modul</th>
                        <th class="py-3 px-4">Objek / ID Record</th>
                        <th class="py-3 px-4">IP Address</th>
                        <th class="py-3 px-4 text-right">Rincian</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($logs as $log)
                        @php
                            $actionBadge = match(true) {
                                str_contains(strtoupper($log['action'] ?? ''), 'CREATE') || str_contains(strtoupper($log['action'] ?? ''), 'SUBMIT') => 'bg-emerald-50 text-[#008B5A] border-emerald-200',
                                str_contains(strtoupper($log['action'] ?? ''), 'UPDATE') || str_contains(strtoupper($log['action'] ?? ''), 'VERIFY') => 'bg-blue-50 text-[#00529B] border-blue-200',
                                str_contains(strtoupper($log['action'] ?? ''), 'LOGIN') => 'bg-purple-50 text-purple-700 border-purple-200',
                                str_contains(strtoupper($log['action'] ?? ''), 'DELETE') => 'bg-red-50 text-[#DC2626] border-red-200',
                                default => 'bg-slate-50 text-slate-700 border-slate-200'
                            };

                            $moduleBadge = match(strtoupper($log['module'] ?? '')) {
                                'AUTH' => 'bg-purple-100 text-purple-800',
                                'INSPECTIONS' => 'bg-blue-100 text-[#00529B]',
                                'FINDINGS' => 'bg-amber-100 text-amber-800',
                                'SARFAS' => 'bg-emerald-100 text-[#008B5A]',
                                'USERS' => 'bg-red-100 text-[#D71920]',
                                default => 'bg-slate-100 text-slate-800'
                            };
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="font-medium text-slate-800">
                                    {{ !empty($log['created_at']) ? date('d/m/Y H:i:s', strtotime($log['created_at'])) : '-' }}
                                </div>
                                <div class="text-[10px] text-slate-400">
                                    WIB
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex items-center space-x-2">
                                    <div class="w-6 h-6 rounded-full bg-slate-200 text-slate-700 font-bold flex items-center justify-center text-[10px] uppercase">
                                        {{ substr($log['user_name'] ?? 'U', 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-800">{{ $log['user_name'] ?? 'Sistem' }}</div>
                                        <div class="text-[10px] text-slate-400">ID: #{{ $log['user_id'] ?? '-' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold border {{ $actionBadge }}">
                                    {{ $log['action'] ?? '-' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $moduleBadge }}">
                                    {{ $log['module'] ?? '-' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-mono font-medium text-slate-700">
                                {{ $log['record_id'] ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 font-mono text-[11px] text-slate-500">
                                {{ $log['ip_address'] ?? '127.0.0.1' }}
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <button type="button" 
                                        @click="selectedLog = {{ json_encode($log) }}; detailModalOpen = true"
                                        class="px-2.5 py-1 text-slate-600 bg-slate-100 hover:bg-slate-200 rounded text-[11px] font-semibold transition">
                                    Lihat Detail
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">
                                Belum ada riwayat aktivitas yang tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL: DETAIL AUDIT LOG -->
    <div x-show="detailModalOpen" 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4" 
         x-cloak>
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-slate-100" @click.away="detailModalOpen = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                <h3 class="text-sm font-bold text-slate-800">Detail Rekaman Jejak Audit</h3>
                <button @click="detailModalOpen = false" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>

            <template x-if="selectedLog">
                <div class="space-y-3 text-xs">
                    <div class="grid grid-cols-2 gap-2 p-3 bg-slate-50 rounded-lg border border-slate-200">
                        <div>
                            <span class="text-slate-400 text-[10px] block">Aksi / Aktivitas:</span>
                            <span class="font-bold text-slate-800" x-text="selectedLog.action"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 text-[10px] block">Modul:</span>
                            <span class="font-bold text-slate-800" x-text="selectedLog.module"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 text-[10px] block">Operator:</span>
                            <span class="font-semibold text-slate-800" x-text="selectedLog.user_name || 'Sistem'"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 text-[10px] block">Waktu:</span>
                            <span class="font-medium text-slate-800" x-text="selectedLog.created_at"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 text-[10px] block">Record Target ID:</span>
                            <span class="font-mono text-slate-800" x-text="selectedLog.record_id || '-'"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 text-[10px] block">IP Address:</span>
                            <span class="font-mono text-slate-800" x-text="selectedLog.ip_address || '127.0.0.1'"></span>
                        </div>
                    </div>

                    <div>
                        <span class="text-slate-500 text-[11px] font-semibold block mb-1">Rincian Payload / Perubahan (Details):</span>
                        <div class="p-3 bg-slate-900 text-slate-100 font-mono text-[11px] rounded-lg overflow-x-auto max-h-60" 
                             x-text="typeof selectedLog.details === 'string' ? selectedLog.details : JSON.stringify(selectedLog.details, null, 2)">
                        </div>
                    </div>
                </div>
            </template>

            <div class="flex items-center justify-end pt-4 border-t border-slate-100 mt-4">
                <button type="button" @click="detailModalOpen = false" class="px-4 py-2 text-xs font-semibold text-white bg-slate-800 hover:bg-slate-900 rounded-lg transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
