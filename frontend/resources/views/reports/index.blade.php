@extends('layouts.app')

@section('title', 'Laporan & Rekapitulasi Inspeksi Sarfas')
@section('breadcrumb', 'Pelaporan / Laporan Inspeksi')
@section('page_title', 'Laporan Hasil Inspeksi Sarfas HSSE')

@section('content')
<div class="space-y-6">

    <!-- Filters Bar -->
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">Filter Data Laporan</h3>
                <p class="text-[11px] text-slate-500">Hasil rekapitulasi dan file ekspor akan menyesuaikan filter aktif di bawah ini.</p>
            </div>
            <div class="flex items-center space-x-2">
                <!-- Export CSV -->
                <a href="{{ route('reports.export', array_merge(request()->query(), ['format' => 'csv'])) }}" 
                   class="px-3.5 py-1.5 text-xs font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-lg shadow-xs transition flex items-center">
                    <svg class="w-4 h-4 mr-1.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Export Excel / CSV
                </a>
                <!-- Print Preview / PDF -->
                <a href="{{ route('reports.export', array_merge(request()->query(), ['format' => 'print'])) }}" target="_blank"
                   class="px-3.5 py-1.5 text-xs font-semibold text-white bg-[#00529B] hover:bg-[#003e75] rounded-lg shadow-xs transition flex items-center">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Pratinjau Cetak Lembar Resmi
                </a>
            </div>
        </div>

        <form method="GET" action="{{ route('reports.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ $filters['start_date'] ?? '' }}"
                       class="w-full px-3 py-1.5 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none">
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $filters['end_date'] ?? '' }}"
                       class="w-full px-3 py-1.5 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none">
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Kategori Sarfas</label>
                <select name="category_id" class="w-full px-3 py-1.5 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none bg-white">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat['id'] }}" {{ ($filters['category_id'] ?? '') == $cat['id'] ? 'selected' : '' }}>
                            {{ $cat['name'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Lokasi Area</label>
                <select name="location_id" class="w-full px-3 py-1.5 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none bg-white">
                    <option value="">Semua Lokasi</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc['id'] }}" {{ ($filters['location_id'] ?? '') == $loc['id'] ? 'selected' : '' }}>
                            {{ $loc['name'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full py-1.5 px-3 text-xs font-semibold text-white bg-[#D71920] hover:bg-[#b8141a] rounded-lg transition shadow-xs flex items-center justify-center">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Terapkan Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Filtered Summary KPI Banner -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3.5">
        <div class="p-3.5 bg-white rounded-xl border border-slate-200 shadow-xs">
            <span class="text-[10px] text-slate-400 block uppercase font-bold">Total Inspeksi</span>
            <div class="text-xl font-bold text-slate-900 mt-1">{{ $summary['totalInspections'] ?? 0 }}</div>
            <span class="text-[10px] text-slate-500">Sesuai filter aktif</span>
        </div>
        <div class="p-3.5 bg-white rounded-xl border border-slate-200 shadow-xs">
            <span class="text-[10px] text-slate-400 block uppercase font-bold">Telah Diverifikasi</span>
            <div class="text-xl font-bold text-[#00529B] mt-1">{{ $summary['completedInspections'] ?? 0 }}</div>
            <span class="text-[10px] text-slate-500">Status selesai</span>
        </div>
        <div class="p-3.5 bg-white rounded-xl border border-slate-200 shadow-xs">
            <span class="text-[10px] text-slate-400 block uppercase font-bold">Kondisi Baik</span>
            <div class="text-xl font-bold text-[#008B5A] mt-1">{{ $summary['goodConditionCount'] ?? 0 }}</div>
            <span class="text-[10px] text-emerald-600 font-semibold">{{ $summary['complianceRate'] ?? 0 }}% Kepatuhan</span>
        </div>
        <div class="p-3.5 bg-white rounded-xl border border-slate-200 shadow-xs">
            <span class="text-[10px] text-slate-400 block uppercase font-bold">Perlu Tindak Lanjut</span>
            <div class="text-xl font-bold text-[#F59E0B] mt-1">{{ $summary['actionNeededCount'] ?? 0 }}</div>
            <span class="text-[10px] text-slate-500">Ketidaksesuaian</span>
        </div>
        <div class="p-3.5 bg-white rounded-xl border border-red-200 bg-red-50/20 shadow-xs">
            <span class="text-[10px] text-red-600 block uppercase font-bold">Temuan Kritis</span>
            <div class="text-xl font-bold text-red-600 mt-1">{{ $summary['criticalCount'] ?? 0 }}</div>
            <span class="text-[10px] text-red-500">Perhatian darurat</span>
        </div>
        <div class="p-3.5 bg-white rounded-xl border border-slate-200 shadow-xs">
            <span class="text-[10px] text-slate-400 block uppercase font-bold">Tingkat Kepatuhan</span>
            <div class="text-xl font-bold text-slate-900 mt-1">{{ $summary['complianceRate'] ?? 0 }}%</div>
            <span class="text-[10px] text-slate-500">Target HSSE > 95%</span>
        </div>
    </div>

    <!-- Report Table -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-200 bg-slate-50/50 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800">Tabel Rincian Hasil Inspeksi (Sesuai Filter)</h3>
            <span class="text-xs text-slate-500">{{ count($records) }} Baris Data</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-100/70 text-slate-700 font-bold uppercase text-[10px]">
                        <th class="p-3">No</th>
                        <th class="p-3">No Inspeksi</th>
                        <th class="p-3">Tanggal</th>
                        <th class="p-3">Sarfas & Kode</th>
                        <th class="p-3">Kategori</th>
                        <th class="p-3">Lokasi</th>
                        <th class="p-3">Inspektor</th>
                        <th class="p-3">Kondisi Hasil</th>
                        <th class="p-3 text-center">Temuan</th>
                        <th class="p-3">Status</th>
                        <th class="p-3 text-right">Rincian</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($records as $idx => $r)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="p-3 text-slate-400 font-mono">{{ $idx + 1 }}</td>
                            <td class="p-3 font-mono font-bold text-slate-900">{{ $r['inspection_no'] }}</td>
                            <td class="p-3">{{ date('d M Y', strtotime($r['inspection_date'])) }}</td>
                            <td class="p-3">
                                <div class="font-semibold text-slate-900">{{ $r['sarfas_name'] }}</div>
                                <span class="text-[10px] text-slate-400 font-mono">{{ $r['asset_code'] }}</span>
                            </td>
                            <td class="p-3 text-slate-600">{{ $r['category_name'] }}</td>
                            <td class="p-3 text-slate-600">{{ $r['location_name'] }}</td>
                            <td class="p-3 text-slate-700 font-medium">{{ $r['inspector_name'] }}</td>
                            <td class="p-3">
                                @if($r['summary_condition'] === 'BAIK')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">BAIK</span>
                                @elseif($r['summary_condition'] === 'KRITIS')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-800">KRITIS</span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">PERLU TINDAK LANJUT</span>
                                @endif
                            </td>
                            <td class="p-3 text-center">
                                @if(($r['total_findings'] ?? 0) > 0)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-700">
                                        {{ $r['total_findings'] }} Temuan
                                    </span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="p-3">
                                @if($r['status'] === 'SELESAI')
                                    <span class="text-emerald-700 font-semibold text-[11px]">Selesai</span>
                                @elseif($r['status'] === 'MENUNGGU_REVIEW')
                                    <span class="text-blue-700 font-semibold text-[11px]">Menunggu Review</span>
                                @else
                                    <span class="text-slate-500 text-[11px]">Draft</span>
                                @endif
                            </td>
                            <td class="p-3 text-right">
                                <a href="{{ route('inspections.show', $r['id']) }}" class="text-[#00529B] font-semibold hover:underline">
                                    Buka &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="p-8 text-center text-slate-400">
                                Tidak ada data laporan inspeksi yang memenuhi filter yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
