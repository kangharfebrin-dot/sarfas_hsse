@extends('layouts.app')

@section('title', 'Daftar Checklist Inspeksi')
@section('breadcrumb', 'Pemeriksaan / Checklist Inspeksi')
@section('page_title', 'Checklist Pemeriksaan Sarfas HSSE')

@section('content')
<div class="space-y-6">

    <!-- Header & Action -->
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <!-- Filter Form -->
        <form method="GET" action="{{ route('inspections.index') }}" class="flex-1 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5">
            <div>
                <select name="status" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none">
                    <option value="">-- Semua Status --</option>
                    <option value="DRAFT" {{ ($filters['status'] ?? '') == 'DRAFT' ? 'selected' : '' }}>Draft</option>
                    <option value="SEDANG_BERLANGSUNG" {{ ($filters['status'] ?? '') == 'SEDANG_BERLANGSUNG' ? 'selected' : '' }}>Sedang Berlangsung</option>
                    <option value="MENUNGGU_REVIEW" {{ ($filters['status'] ?? '') == 'MENUNGGU_REVIEW' ? 'selected' : '' }}>Menunggu Review</option>
                    <option value="SELESAI" {{ ($filters['status'] ?? '') == 'SELESAI' ? 'selected' : '' }}>Selesai Disetujui</option>
                    <option value="DITOLAK" {{ ($filters['status'] ?? '') == 'DITOLAK' ? 'selected' : '' }}>Ditolak / Revisi</option>
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
            <div>
                <input type="date" name="start_date" value="{{ $filters['start_date'] ?? '' }}" placeholder="Dari tanggal"
                       class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none">
            </div>
            <div class="flex space-x-2">
                <input type="date" name="end_date" value="{{ $filters['end_date'] ?? '' }}" placeholder="Sampai tanggal"
                       class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none">
                <button type="submit" class="px-3.5 py-2 text-xs font-semibold text-white bg-[#00529B] hover:bg-[#003e75] rounded-lg transition shrink-0">
                    Filter
                </button>
            </div>
        </form>

        <a href="{{ route('inspections.create') }}" class="px-4 py-2 text-xs font-semibold text-white bg-[#D71920] hover:bg-[#b8141a] rounded-lg shadow-xs transition flex items-center shrink-0 justify-center">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Mulai Inspeksi Baru
        </a>
    </div>

    <!-- Inspections Table -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50/80 text-slate-700 font-bold uppercase tracking-wider text-[10px]">
                        <th class="p-3.5">No Inspeksi</th>
                        <th class="p-3.5">Sarfas & Lokasi</th>
                        <th class="p-3.5">Template Checklist</th>
                        <th class="p-3.5">Tanggal</th>
                        <th class="p-3.5">Pemeriksa (Inspector)</th>
                        <th class="p-3.5">Kondisi Hasil</th>
                        <th class="p-3.5">Status</th>
                        <th class="p-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($inspections as $insp)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="p-3.5 font-mono font-bold text-slate-900">
                                {{ $insp['inspection_no'] }}
                            </td>
                            <td class="p-3.5">
                                <div class="font-semibold text-slate-900">{{ $insp['sarfas_name'] }}</div>
                                <div class="text-[11px] text-slate-500 font-mono">{{ $insp['asset_code'] }} · {{ $insp['location_name'] }}</div>
                            </td>
                            <td class="p-3.5 text-slate-700">
                                <div class="font-medium text-slate-800">{{ $insp['template_title'] }}</div>
                                <div class="text-[10px] text-slate-400">{{ $insp['category_name'] }}</div>
                            </td>
                            <td class="p-3.5 text-slate-600 font-medium">
                                {{ date('d M Y', strtotime($insp['inspection_date'])) }}
                            </td>
                            <td class="p-3.5 text-slate-700">
                                <div class="font-semibold text-slate-800">{{ $insp['inspector_name'] }}</div>
                                @if($insp['verifier_name'])
                                    <div class="text-[10px] text-emerald-600 font-medium">Verifikator: {{ $insp['verifier_name'] }}</div>
                                @endif
                            </td>
                            <td class="p-3.5">
                                @if($insp['summary_condition'] === 'BAIK')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 mr-1.5"></span> BAIK
                                    </span>
                                @elseif($insp['summary_condition'] === 'KRITIS')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-800 border border-red-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-600 mr-1.5"></span> KRITIS
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-600 mr-1.5"></span> PERLU TINDAK LANJUT
                                    </span>
                                @endif
                            </td>
                            <td class="p-3.5">
                                @if($insp['status'] === 'SELESAI')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Selesai Diverifikasi
                                    </span>
                                @elseif($insp['status'] === 'MENUNGGU_REVIEW')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                        Menunggu Review
                                    </span>
                                @elseif($insp['status'] === 'DRAFT')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700">
                                        Draft
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-rose-100 text-rose-700">
                                        {{ $insp['status'] }}
                                    </span>
                                @endif
                            </td>
                            <td class="p-3.5 text-right space-x-1.5">
                                @if($insp['status'] === 'DRAFT')
                                    <a href="{{ route('inspections.form', $insp['id']) }}" class="inline-flex items-center px-2.5 py-1 text-xs font-semibold text-white bg-[#00529B] hover:bg-[#003e75] rounded transition">
                                        Lanjutkan Draft
                                    </a>
                                @else
                                    <a href="{{ route('inspections.show', $insp['id']) }}" class="inline-flex items-center px-2.5 py-1 text-xs font-semibold text-[#00529B] bg-blue-50 hover:bg-blue-100 rounded transition">
                                        Lihat Hasil
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-8 text-center text-slate-400">
                                Tidak ada catatan inspeksi yang sesuai filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
