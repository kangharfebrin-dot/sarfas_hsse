@extends('layouts.app')

@section('title', 'Temuan & Tindak Lanjut HSSE')
@section('breadcrumb', 'Pemantauan / Temuan & Tindak Lanjut')
@section('page_title', 'Monitoring Temuan Ketidaksesuaian Sarfas')

@section('content')
<div class="space-y-6">

    <!-- Filters Bar -->
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <form method="GET" action="{{ route('findings.index') }}" class="flex-1 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5">
            <div>
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Cari nomor temuan, sarfas..." 
                       class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none">
            </div>
            <div>
                <select name="status" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none">
                    <option value="">-- Semua Status --</option>
                    <option value="OPEN" {{ ($filters['status'] ?? '') == 'OPEN' ? 'selected' : '' }}>Temuan Belum Selesai (Open)</option>
                    <option value="BARU" {{ ($filters['status'] ?? '') == 'BARU' ? 'selected' : '' }}>Baru</option>
                    <option value="DALAM_TINDAK_LANJUT" {{ ($filters['status'] ?? '') == 'DALAM_TINDAK_LANJUT' ? 'selected' : '' }}>Dalam Tindak Lanjut</option>
                    <option value="MENUNGGU_VERIFIKASI" {{ ($filters['status'] ?? '') == 'MENUNGGU_VERIFIKASI' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                    <option value="SELESAI" {{ ($filters['status'] ?? '') == 'SELESAI' ? 'selected' : '' }}>Selesai / Closed</option>
                </select>
            </div>
            <div>
                <select name="risk_level" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none">
                    <option value="">-- Tingkat Risiko --</option>
                    <option value="KRITIS" {{ ($filters['risk_level'] ?? '') == 'KRITIS' ? 'selected' : '' }}>Kritis (SLA 3 Hari)</option>
                    <option value="TINGGI" {{ ($filters['risk_level'] ?? '') == 'TINGGI' ? 'selected' : '' }}>Tinggi</option>
                    <option value="SEDANG" {{ ($filters['risk_level'] ?? '') == 'SEDANG' ? 'selected' : '' }}>Sedang</option>
                    <option value="RENDAH" {{ ($filters['risk_level'] ?? '') == 'RENDAH' ? 'selected' : '' }}>Rendah</option>
                </select>
            </div>
            <div class="flex space-x-2">
                <select name="sarfas_id" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none">
                    <option value="">-- Sarfas --</option>
                    @foreach($sarfasList as $s)
                        <option value="{{ $s['id'] }}" {{ ($filters['sarfas_id'] ?? '') == $s['id'] ? 'selected' : '' }}>
                            {{ $s['asset_code'] }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="px-3.5 py-2 text-xs font-semibold text-white bg-[#00529B] hover:bg-[#003e75] rounded-lg transition shrink-0">
                    Filter
                </button>
            </div>
        </form>

        <div class="flex items-center space-x-2 shrink-0">
            <a href="{{ route('findings.index', ['risk_level' => 'KRITIS']) }}" class="px-3 py-1.5 text-xs font-bold rounded-lg bg-red-100 text-red-700 hover:bg-red-200 transition">
                Filter Kritis Saja
            </a>
        </div>
    </div>

    <!-- Findings Table -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50/80 text-slate-700 font-bold uppercase tracking-wider text-[10px]">
                        <th class="p-3.5">No Temuan</th>
                        <th class="p-3.5">Sarfas & Lokasi</th>
                        <th class="p-3.5">Uraian Ketidaksesuaian</th>
                        <th class="p-3.5">Risiko HSSE</th>
                        <th class="p-3.5">Penanggung Jawab (PIC)</th>
                        <th class="p-3.5">Tenggat Waktu</th>
                        <th class="p-3.5">Status</th>
                        <th class="p-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($findings as $find)
                        @php
                            $isOverdue = $find['status'] !== 'SELESAI' && $find['due_date'] && strtotime($find['due_date']) < time();
                        @endphp
                        <tr class="hover:bg-slate-50 transition {{ $find['risk_level'] === 'KRITIS' && $find['status'] !== 'SELESAI' ? 'bg-red-50/20' : '' }}">
                            <td class="p-3.5 font-mono font-bold text-slate-900">
                                {{ $find['finding_no'] }}
                            </td>
                            <td class="p-3.5">
                                <div class="font-semibold text-slate-900">{{ $find['sarfas_name'] }}</div>
                                <div class="text-[11px] text-slate-500 font-mono">{{ $find['asset_code'] }} · {{ $find['location_name'] }}</div>
                            </td>
                            <td class="p-3.5 max-w-xs">
                                <div class="font-semibold text-slate-800 line-clamp-1">{{ $find['title'] }}</div>
                                <p class="text-[11px] text-slate-500 line-clamp-1 mt-0.5">{{ $find['description'] }}</p>
                            </td>
                            <td class="p-3.5">
                                @if($find['risk_level'] === 'KRITIS')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-800 border border-red-200">
                                        <svg class="w-3 h-3 mr-1 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                        KRITIS
                                    </span>
                                @elseif($find['risk_level'] === 'TINGGI')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-orange-100 text-orange-800 border border-orange-200">
                                        TINGGI
                                    </span>
                                @elseif($find['risk_level'] === 'SEDANG')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                        SEDANG
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">
                                        RENDAH
                                    </span>
                                @endif
                            </td>
                            <td class="p-3.5 text-slate-700">
                                <div class="font-medium text-slate-800">{{ $find['assigned_to_name'] ?? 'Belum Ditugaskan' }}</div>
                                <div class="text-[10px] text-slate-400">Pelapor: {{ $find['reported_by_name'] }}</div>
                            </td>
                            <td class="p-3.5">
                                @if($find['due_date'])
                                    <div class="{{ $isOverdue ? 'text-red-600 font-bold' : 'text-slate-600' }}">
                                        {{ date('d M Y', strtotime($find['due_date'])) }}
                                    </div>
                                    @if($isOverdue)
                                        <span class="text-[9px] font-bold text-red-600 uppercase block">Terlewat</span>
                                    @endif
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="p-3.5">
                                @if($find['status'] === 'SELESAI')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        SELESAI DITUTUP
                                    </span>
                                @elseif($find['status'] === 'MENUNGGU_VERIFIKASI')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                        MENUNGGU VERIFIKASI
                                    </span>
                                @elseif($find['status'] === 'DALAM_TINDAK_LANJUT')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                        DALAM PENANGANAN
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-800 border border-red-200">
                                        TEMUAN BARU
                                    </span>
                                @endif
                            </td>
                            <td class="p-3.5 text-right">
                                <a href="{{ route('findings.show', $find['id']) }}" class="inline-flex items-center px-2.5 py-1 text-xs font-semibold text-[#00529B] bg-blue-50 hover:bg-blue-100 rounded transition">
                                    Tindak Lanjut &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-8 text-center text-slate-400">
                                Tidak ada temuan yang sesuai dengan filter pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
