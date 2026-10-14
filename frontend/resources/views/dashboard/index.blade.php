@extends('layouts.app')

@section('title', 'Dashboard Operasional HSSE')
@section('breadcrumb', 'PT Pertamina Patra Niaga / Fuel Terminal Maos')
@section('page_title', 'Dashboard Monitoring Sarfas HSSE')

@section('content')
<div class="space-y-6">

    @if(!empty($apiError))
        <div class="p-4 bg-amber-50 border border-amber-300 rounded-xl text-amber-900 flex items-start space-x-3 shadow-xs">
            <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div class="text-xs">
                <div class="font-bold text-sm text-amber-950">Gangguan Komunikasi Backend API</div>
                <p class="mt-0.5 text-amber-800">Layanan data operasional tidak dapat dimuat dari backend Express REST API (Port 3001). Status indikator diatur ke nol untuk mencegah penyajian informasi yang tidak akurat. Silakan periksa status service backend.</p>
            </div>
        </div>
    @endif

    <!-- Top Alert Banner: Context Info -->
    <div class="p-4 bg-white border border-slate-200 rounded-xl shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center space-x-3.5">
            <div class="w-10 h-10 rounded-lg bg-[#00529B]/10 text-[#00529B] flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
            <div>
                <h2 class="text-sm font-bold text-slate-800">Fuel Terminal Maos — Sarana & Fasilitas HSSE</h2>
                <p class="text-xs text-slate-500">Ringkasan status inspeksi berkala, sarfas proteksi kebakaran, dan tindak lanjut temuan lapangan.</p>
            </div>
        </div>
        <div class="flex items-center space-x-2 shrink-0">
            <a href="{{ route('inspections.create') }}" class="px-3.5 py-2 text-xs font-semibold text-white bg-[#D71920] hover:bg-[#b8141a] rounded-lg shadow-xs transition flex items-center">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Mulai Inspeksi Baru
            </a>
            <a href="{{ route('reports.index') }}" class="px-3 py-2 text-xs font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition flex items-center">
                <svg class="w-4 h-4 mr-1.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Laporan
            </a>
        </div>
    </div>

    <!-- 6 KPI SUMMARY CARDS -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3.5">
        <!-- 1. Total Sarfas -->
        <div class="p-4 bg-white border border-slate-200 rounded-xl shadow-xs">
            <div class="flex items-center justify-between text-slate-500 text-xs font-medium">
                <span>Total Sarfas</span>
                <span class="p-1 rounded bg-slate-100 text-slate-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </span>
            </div>
            <div class="mt-2 text-2xl font-bold text-slate-900">{{ $cards['totalSarfas'] }}</div>
            <div class="mt-1 text-[11px] text-slate-500">Unit terdaftar aktif</div>
        </div>

        <!-- 2. Inspeksi Bulan Ini -->
        <div class="p-4 bg-white border border-slate-200 rounded-xl shadow-xs">
            <div class="flex items-center justify-between text-slate-500 text-xs font-medium">
                <span>Inspeksi Bulan Ini</span>
                <span class="p-1 rounded bg-blue-50 text-[#00529B]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                </span>
            </div>
            <div class="mt-2 text-2xl font-bold text-[#00529B]">{{ $cards['inspeksiBulanIni'] }}</div>
            <div class="mt-1 text-[11px] text-slate-500">Periode Oktober 2026</div>
        </div>

        <!-- 3. Sarfas Kondisi Baik -->
        <div class="p-4 bg-white border border-slate-200 rounded-xl shadow-xs">
            <div class="flex items-center justify-between text-slate-500 text-xs font-medium">
                <span>Kondisi Baik</span>
                <span class="p-1 rounded bg-emerald-50 text-[#008B5A]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </span>
            </div>
            <div class="mt-2 text-2xl font-bold text-[#008B5A]">{{ $cards['sarfasBaik'] }} <span class="text-xs font-normal text-slate-400">/ {{ $cards['totalSarfas'] }}</span></div>
            <div class="mt-1 text-[11px] text-emerald-600 font-semibold">{{ $cards['goodConditionPct'] }}% Siap Operasi</div>
        </div>

        <!-- 4. Temuan Belum Selesai -->
        <div class="p-4 bg-white border border-slate-200 rounded-xl shadow-xs">
            <div class="flex items-center justify-between text-slate-500 text-xs font-medium">
                <span>Temuan Open</span>
                <span class="p-1 rounded bg-amber-50 text-[#F59E0B]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </span>
            </div>
            <div class="mt-2 text-2xl font-bold text-[#F59E0B]">{{ $cards['temuanBelumSelesai'] }}</div>
            <div class="mt-1 text-[11px] text-slate-500">Perlu tindak lanjut</div>
        </div>

        <!-- 5. Temuan Kritis -->
        <div class="p-4 bg-white border border-red-200 rounded-xl shadow-xs bg-red-50/20">
            <div class="flex items-center justify-between text-red-600 text-xs font-medium">
                <span class="font-bold">Temuan Kritis</span>
                <span class="p-1 rounded bg-red-100 text-red-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
            </div>
            <div class="mt-2 text-2xl font-bold text-[#DC2626]">{{ $cards['temuanKritis'] }}</div>
            <div class="mt-1 text-[11px] text-red-600 font-semibold">Tindakan segera (1-3 hari)</div>
        </div>

        <!-- 6. Inspeksi Terlambat -->
        <div class="p-4 bg-white border border-slate-200 rounded-xl shadow-xs">
            <div class="flex items-center justify-between text-slate-500 text-xs font-medium">
                <span>Inspeksi Terlambat</span>
                <span class="p-1 rounded bg-rose-50 text-rose-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
            </div>
            <div class="mt-2 text-2xl font-bold text-rose-600">{{ $cards['inspeksiTerlambat'] }}</div>
            <div class="mt-1 text-[11px] text-slate-500">Melewati tenggat jadwal</div>
        </div>
    </div>

    <!-- CHARTS SECTION: Trends & Category Breakdown -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Monthly Inspection Trends Chart -->
        <div class="lg:col-span-8 p-5 bg-white border border-slate-200 rounded-xl shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-800">Tren Inspeksi & Kepatuhan Sarfas (6 Bulan Terakhir)</h3>
                    <p class="text-xs text-slate-500">Perbandingan volume inspeksi selesai terhadap temuan ketidaksesuaian</p>
                </div>
                <span class="text-xs font-medium px-2 py-1 rounded bg-slate-100 text-slate-600">Mei - Okt 2026</span>
            </div>
            <div class="relative h-64 w-full">
                <canvas id="inspectionTrendsChart"></canvas>
            </div>
        </div>

        <!-- Category Distribution Doughnut -->
        <div class="lg:col-span-4 p-5 bg-white border border-slate-200 rounded-xl shadow-xs flex flex-col justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-800">Distribusi Sarfas per Kategori</h3>
                <p class="text-xs text-slate-500">Kategori peralatan keselamatan di FT Maos</p>
                <div class="relative h-48 w-full mt-2">
                    <canvas id="categoryDoughnutChart"></canvas>
                </div>
            </div>
            <div class="pt-3 border-t border-slate-100 text-[11px] text-slate-500 flex justify-between">
                <span>Total 8 Kategori Sarfas</span>
                <a href="{{ route('sarfas.index') }}" class="text-[#00529B] font-semibold hover:underline">Lihat Semua &rarr;</a>
            </div>
        </div>
    </div>

    <!-- 2 COLUMN OPERATIONAL TABLES -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Left: High Priority Findings & Recent Inspections -->
        <div class="lg:col-span-7 space-y-6">
            
            <!-- Temuan Prioritas Tinggi -->
            <div class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
                <div class="p-4 border-b border-slate-200 flex items-center justify-between bg-slate-50/50">
                    <div class="flex items-center space-x-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#DC2626]"></span>
                        <h3 class="text-sm font-bold text-slate-800">Temuan Prioritas Tinggi & Kritis</h3>
                    </div>
                    <a href="{{ route('findings.index', ['status' => 'OPEN']) }}" class="text-xs text-[#00529B] font-semibold hover:underline">Kelola Temuan &rarr;</a>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse($highPriorityFindings as $finding)
                        <div class="p-4 hover:bg-slate-50 transition flex items-start justify-between gap-4">
                            <div class="space-y-1">
                                <div class="flex items-center space-x-2">
                                    <span class="text-xs font-bold text-slate-900">{{ $finding['finding_no'] }}</span>
                                    @if($finding['risk_level'] === 'KRITIS')
                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded bg-red-100 text-red-700 border border-red-200">KRITIS</span>
                                    @elseif($finding['risk_level'] === 'TINGGI')
                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded bg-orange-100 text-orange-700">TINGGI</span>
                                    @else
                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded bg-amber-100 text-amber-700">SEDANG</span>
                                    @endif
                                    <span class="text-xs text-slate-500 font-mono">{{ $finding['asset_code'] }}</span>
                                </div>
                                <h4 class="text-xs font-semibold text-slate-800">{{ $finding['title'] }}</h4>
                                <div class="text-[11px] text-slate-500 flex items-center space-x-3">
                                    <span>Sarfas: {{ $finding['sarfas_name'] }}</span>
                                    <span>&bull;</span>
                                    <span>PIC: {{ $finding['assigned_name'] ?? 'Belum Ditugaskan' }}</span>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <div class="text-[11px] font-medium {{ strtotime($finding['due_date']) < time() ? 'text-red-600 font-bold' : 'text-slate-500' }}">
                                    Tenggat: {{ date('d M Y', strtotime($finding['due_date'])) }}
                                </div>
                                <a href="{{ route('findings.show', $finding['id']) }}" class="mt-2 inline-block px-2.5 py-1 text-xs font-semibold text-[#00529B] bg-blue-50 hover:bg-blue-100 rounded transition">
                                    Tindak Lanjut &rarr;
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center text-xs text-slate-400">Tidak ada temuan kritis yang belum diselesaikan.</div>
                    @endforelse
                </div>
            </div>

            <!-- Inspeksi Terbaru -->
            <div class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
                <div class="p-4 border-b border-slate-200 flex items-center justify-between bg-slate-50/50">
                    <h3 class="text-sm font-bold text-slate-800">Catatan Inspeksi Terakhir</h3>
                    <a href="{{ route('inspections.index') }}" class="text-xs text-[#00529B] font-semibold hover:underline">Semua Inspeksi &rarr;</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50/80 text-slate-600 font-semibold">
                                <th class="p-3">No Inspeksi</th>
                                <th class="p-3">Sarfas</th>
                                <th class="p-3">Pemeriksa</th>
                                <th class="p-3">Hasil</th>
                                <th class="p-3">Status</th>
                                <th class="p-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse($recentInspections as $insp)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="p-3 font-semibold text-slate-900">{{ $insp['inspection_no'] }}</td>
                                    <td class="p-3">
                                        <div class="font-medium text-slate-800">{{ $insp['sarfas_name'] }}</div>
                                        <div class="text-[10px] text-slate-400 font-mono">{{ $insp['asset_code'] }}</div>
                                    </td>
                                    <td class="p-3 text-slate-600">{{ $insp['inspector_name'] }}</td>
                                    <td class="p-3">
                                        @if($insp['summary_condition'] === 'BAIK')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">BAIK</span>
                                        @elseif($insp['summary_condition'] === 'KRITIS')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-800">KRITIS</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">PERLU TINDAK LANJUT</span>
                                        @endif
                                    </td>
                                    <td class="p-3">
                                        @if($insp['status'] === 'SELESAI')
                                            <span class="text-slate-600 font-medium">Selesai Diverifikasi</span>
                                        @elseif($insp['status'] === 'MENUNGGU_REVIEW')
                                            <span class="text-blue-600 font-medium">Menunggu Review</span>
                                        @else
                                            <span class="text-slate-400">Draft</span>
                                        @endif
                                    </td>
                                    <td class="p-3 text-right">
                                        <a href="{{ route('inspections.show', $insp['id']) }}" class="text-[#00529B] hover:underline font-semibold">Buka</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-4 text-center text-slate-400">Belum ada catatan inspeksi.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Right: Upcoming Schedules & Recent Activity Log -->
        <div class="lg:col-span-5 space-y-6">

            <!-- Jadwal Inspeksi Mendatang -->
            <div class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
                <div class="p-4 border-b border-slate-200 flex items-center justify-between bg-slate-50/50">
                    <div class="flex items-center space-x-2">
                        <svg class="w-4 h-4 text-[#00529B]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <h3 class="text-sm font-bold text-slate-800">Jadwal Inspeksi Terdekat</h3>
                    </div>
                    <a href="{{ route('schedules.index') }}" class="text-xs text-[#00529B] font-semibold hover:underline">Kalender &rarr;</a>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse($upcomingSchedules as $sched)
                        <div class="p-3.5 hover:bg-slate-50 transition flex items-center justify-between">
                            <div>
                                <div class="text-xs font-semibold text-slate-800">{{ $sched['title'] }}</div>
                                <div class="text-[11px] text-slate-500 mt-0.5">Sarfas: {{ $sched['sarfas_name'] }}</div>
                                <div class="text-[10px] text-slate-400 mt-0.5">Petugas: {{ $sched['assigned_user_name'] }}</div>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded {{ $sched['status'] === 'TERLAMBAT' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-800' }}">
                                    {{ $sched['status'] }}
                                </span>
                                <div class="text-[10px] text-slate-500 mt-1 font-medium">{{ date('d M Y', strtotime($sched['scheduled_date'])) }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="p-4 text-center text-xs text-slate-400">Tidak ada jadwal inspeksi aktif.</div>
                    @endforelse
                </div>
            </div>

            <!-- Live Audit Log / Aktivitas Terbaru -->
            <div class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
                <div class="p-4 border-b border-slate-200 flex items-center justify-between bg-slate-50/50">
                    <h3 class="text-sm font-bold text-slate-800">Log Aktivitas Petugas HSSE</h3>
                    <a href="{{ route('audit-logs.index') }}" class="text-xs text-[#00529B] font-semibold hover:underline">Riwayat &rarr;</a>
                </div>
                <div class="p-4 space-y-4">
                    @forelse($recentActivity as $log)
                        <div class="flex items-start space-x-3 text-xs">
                            <div class="w-2 h-2 mt-1.5 rounded-full bg-[#00529B] shrink-0"></div>
                            <div class="flex-1">
                                <p class="text-slate-800 font-medium">
                                    <span class="font-bold">{{ $log['user_name'] ?? 'System' }}</span>: {{ $log['details'] }}
                                </p>
                                <span class="text-[10px] text-slate-400">{{ date('d M Y H:i', strtotime($log['created_at'])) }} WIB</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-xs text-slate-400 py-3">Belum ada riwayat aktivitas tercatat.</div>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // 1. Monthly Trends Chart
        const trendsCtx = document.getElementById('inspectionTrendsChart');
        if (trendsCtx && window.Chart) {
            new Chart(trendsCtx, {
                type: 'bar',
                data: {
                    labels: ['Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt'],
                    datasets: [
                        {
                            label: 'Inspeksi Selesai',
                            data: [24, 28, 31, 35, 40, 38],
                            backgroundColor: '#00529B',
                            borderRadius: 6
                        },
                        {
                            label: 'Temuan Ditemukan',
                            data: [4, 6, 3, 5, 7, 3],
                            backgroundColor: '#D71920',
                            borderRadius: 6
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top', labels: { boxWidth: 12, font: { size: 11 } } }
                    },
                    scales: {
                        y: { beginAtZero: true, grid: { color: '#F1F5F9' } },
                        x: { grid: { display: false } }
                    }
                }
            });
        }

        // 2. Category Breakdown Chart
        const catCtx = document.getElementById('categoryDoughnutChart');
        if (catCtx && window.Chart) {
            new Chart(catCtx, {
                type: 'doughnut',
                data: {
                    labels: ['APAR & Proteksi', 'Hydrant & Foam', 'Eyewash', 'Spill Kit', 'Lainnya'],
                    datasets: [{
                        data: [4, 2, 2, 1, 3],
                        backgroundColor: ['#D71920', '#00529B', '#008B5A', '#F59E0B', '#64748B'],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } }
                    },
                    cutout: '65%'
                }
            });
        }
    });
</script>
@endpush
