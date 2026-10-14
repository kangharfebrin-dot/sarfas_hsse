<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Rekapitulasi Inspeksi Sarfas HSSE - FT Maos</title>
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css'])

    <style>
        body {
            font-family: 'Inter', sans-serif;
            color: #1e293b;
            background-color: #f8fafc;
        }
        @media print {
            body {
                background-color: #ffffff;
                color: #000000;
            }
            .no-print {
                display: none !important;
            }
            .print-shadow-none {
                box-shadow: none !important;
            }
            @page {
                size: A4 landscape;
                margin: 12mm 10mm;
            }
            .page-break-inside-avoid {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body class="p-4 md:p-8">

    <!-- Top Action Bar (Screen Only) -->
    <div class="max-w-7xl mx-auto mb-6 flex flex-wrap items-center justify-between gap-4 p-4 bg-white border border-slate-200 rounded-xl shadow-sm no-print">
        <div class="flex items-center space-x-3">
            <a href="{{ route('reports.index') }}" class="inline-flex items-center px-3.5 py-2 text-xs font-semibold text-slate-700 bg-slate-100 rounded-lg hover:bg-slate-200 transition">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Filter Laporan
            </a>
            <span class="text-xs text-slate-400">|</span>
            <span class="text-xs text-slate-600 font-medium">Pratinjau Cetak Formal Dokumen HSSE</span>
        </div>
        <div class="flex items-center space-x-2">
            <button onclick="window.print()" class="inline-flex items-center px-4 py-2 text-xs font-bold text-white bg-[#D71920] rounded-lg shadow-sm hover:bg-[#b5141a] transition">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Cetak / Simpan PDF
            </button>
        </div>
    </div>

    <!-- Official Report Document Container -->
    <div class="max-w-7xl mx-auto bg-white p-8 md:p-10 border border-slate-200 rounded-xl shadow-sm print-shadow-none">
        
        <!-- KOP SURAT / DOKUMEN RESMI PERTAMINA -->
        <div class="border-b-2 border-slate-900 pb-5 mb-6">
            <div class="flex items-start justify-between">
                <div class="flex items-center space-x-4">
                    <!-- Brand Icon Indicator -->
                    <div class="w-14 h-14 bg-[#D71920] rounded-xl flex items-center justify-center text-white font-extrabold shadow-sm shrink-0">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs font-black tracking-widest text-slate-500 uppercase">PT Pertamina Patra Niaga</div>
                        <div class="text-lg font-extrabold text-[#D71920] uppercase tracking-wide">Fuel Terminal Maos — Integrated Terminal Cilacap</div>
                        <div class="text-xs text-slate-600 font-medium">Health, Safety, Security & Environment (HSSE) Department</div>
                        <div class="text-[10px] text-slate-400 mt-0.5">Jl. Stasiun Maos No. 1, Karangreja, Maos, Kabupaten Cilacap, Jawa Tengah 53272</div>
                    </div>
                </div>

                <div class="text-right border-l pl-5 border-slate-200 text-xs">
                    <div class="inline-block px-2.5 py-1 bg-slate-100 font-mono text-[11px] font-bold text-slate-700 rounded border border-slate-300">
                        FORM-HSSE-SARFAS-04
                    </div>
                    <div class="text-[11px] text-slate-500 mt-2">Nomor Dokumen:</div>
                    <div class="font-mono font-semibold text-slate-800">FTM/HSSE/RPT/{{ date('Ym') }}/{{ str_pad(rand(10,99), 3, '0', STR_PAD_LEFT) }}</div>
                    <div class="text-[10px] text-slate-400 mt-1">Dicetak: {{ now()->translatedFormat('d F Y, H:i') }} WIB</div>
                </div>
            </div>

            <div class="mt-5 pt-3 border-t border-slate-200 text-center">
                <h1 class="text-xl font-bold uppercase tracking-wider text-slate-900">LAPORAN REKAPITULASI INSPEKSI SARANA & FASILITAS (SARFAS) HSSE</h1>
                <p class="text-xs text-slate-500 mt-0.5">
                    Periode: 
                    <span class="font-semibold text-slate-700">{{ $filters['start_date'] ?? 'Semua Waktu' }}</span> s/d 
                    <span class="font-semibold text-slate-700">{{ $filters['end_date'] ?? date('Y-m-d') }}</span>
                    @if(!empty($filters['status']))
                        | Status: <span class="font-semibold uppercase text-slate-700">{{ $filters['status'] }}</span>
                    @endif
                </p>
            </div>
        </div>

        <!-- STATISTICAL METRICS SUMMARY (PRINT FRIENDLY) -->
        <div class="grid grid-cols-2 md:grid-cols-6 gap-3 mb-6">
            <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg text-center">
                <div class="text-[10px] font-bold uppercase text-slate-500">Total Inspeksi</div>
                <div class="text-lg font-extrabold text-slate-900 mt-0.5">{{ $summary['totalInspections'] ?? count($records) }}</div>
                <div class="text-[9px] text-slate-400">Tercatat</div>
            </div>
            <div class="p-3 bg-blue-50/50 border border-blue-200 rounded-lg text-center">
                <div class="text-[10px] font-bold uppercase text-[#00529B]">Selesai</div>
                <div class="text-lg font-extrabold text-[#00529B] mt-0.5">{{ $summary['completedInspections'] ?? 0 }}</div>
                <div class="text-[9px] text-blue-500">Completed</div>
            </div>
            <div class="p-3 bg-emerald-50/50 border border-emerald-200 rounded-lg text-center">
                <div class="text-[10px] font-bold uppercase text-[#008B5A]">Kondisi Baik</div>
                <div class="text-lg font-extrabold text-[#008B5A] mt-0.5">{{ $summary['goodConditionCount'] ?? 0 }}</div>
                <div class="text-[9px] text-emerald-600">Sesuai Standar</div>
            </div>
            <div class="p-3 bg-amber-50/50 border border-amber-200 rounded-lg text-center">
                <div class="text-[10px] font-bold uppercase text-amber-700">Perlu Tindak Lanjut</div>
                <div class="text-lg font-extrabold text-[#F59E0B] mt-0.5">{{ $summary['actionNeededCount'] ?? 0 }}</div>
                <div class="text-[9px] text-amber-600">Temuan Aktif</div>
            </div>
            <div class="p-3 bg-red-50/50 border border-red-200 rounded-lg text-center">
                <div class="text-[10px] font-bold uppercase text-[#DC2626]">Temuan Kritis</div>
                <div class="text-lg font-extrabold text-[#DC2626] mt-0.5">{{ $summary['criticalCount'] ?? 0 }}</div>
                <div class="text-[9px] text-red-500">High Risk</div>
            </div>
            <div class="p-3 bg-slate-900 text-white rounded-lg text-center">
                <div class="text-[10px] font-bold uppercase text-slate-300">Tingkat Kepatuhan</div>
                <div class="text-lg font-extrabold text-white mt-0.5">{{ $summary['complianceRate'] ?? 0 }}%</div>
                <div class="text-[9px] text-emerald-400">HSSE Compliance</div>
            </div>
        </div>

        <!-- MAIN DATA TABLE -->
        <div class="overflow-x-auto mb-8">
            <table class="w-full text-left text-xs border border-slate-300 border-collapse">
                <thead>
                    <tr class="bg-slate-100 text-slate-700 uppercase font-bold text-[10px] tracking-wider border-b border-slate-300">
                        <th class="py-2.5 px-3 border-r border-slate-300 text-center w-10">No</th>
                        <th class="py-2.5 px-3 border-r border-slate-300">No. Inspeksi</th>
                        <th class="py-2.5 px-3 border-r border-slate-300">Tanggal</th>
                        <th class="py-2.5 px-3 border-r border-slate-300">Kode Sarfas</th>
                        <th class="py-2.5 px-3 border-r border-slate-300">Nama Sarfas & Area</th>
                        <th class="py-2.5 px-3 border-r border-slate-300">Kategori</th>
                        <th class="py-2.5 px-3 border-r border-slate-300">Pemeriksa</th>
                        <th class="py-2.5 px-3 border-r border-slate-300 text-center">Status</th>
                        <th class="py-2.5 px-3 border-r border-slate-300 text-center">Hasil Kondisi</th>
                        <th class="py-2.5 px-3 text-center">Temuan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($records as $idx => $r)
                        <tr class="{{ $idx % 2 == 0 ? 'bg-white' : 'bg-slate-50/60' }} page-break-inside-avoid">
                            <td class="py-2 px-3 border-r border-slate-200 text-center font-mono text-slate-500">{{ $idx + 1 }}</td>
                            <td class="py-2 px-3 border-r border-slate-200 font-mono font-semibold text-slate-800">{{ $r['inspection_no'] ?? '-' }}</td>
                            <td class="py-2 px-3 border-r border-slate-200 whitespace-nowrap text-slate-600">{{ $r['inspection_date'] ?? '-' }}</td>
                            <td class="py-2 px-3 border-r border-slate-200 font-mono text-slate-700">{{ $r['asset_code'] ?? '-' }}</td>
                            <td class="py-2 px-3 border-r border-slate-200">
                                <div class="font-bold text-slate-900">{{ $r['sarfas_name'] ?? '-' }}</div>
                                <div class="text-[10px] text-slate-500">{{ $r['location_name'] ?? '-' }}</div>
                            </td>
                            <td class="py-2 px-3 border-r border-slate-200 text-slate-600">{{ $r['category_name'] ?? '-' }}</td>
                            <td class="py-2 px-3 border-r border-slate-200 text-slate-700 font-medium">{{ $r['inspector_name'] ?? '-' }}</td>
                            <td class="py-2 px-3 border-r border-slate-200 text-center">
                                @if(($r['status'] ?? '') === 'completed')
                                    <span class="inline-block px-2 py-0.5 text-[9px] font-bold text-emerald-800 bg-emerald-100 rounded">SELESAI</span>
                                @elseif(($r['status'] ?? '') === 'in_progress')
                                    <span class="inline-block px-2 py-0.5 text-[9px] font-bold text-blue-800 bg-blue-100 rounded">PROSES</span>
                                @elseif(($r['status'] ?? '') === 'submitted')
                                    <span class="inline-block px-2 py-0.5 text-[9px] font-bold text-amber-800 bg-amber-100 rounded">REVIEW</span>
                                @else
                                    <span class="inline-block px-2 py-0.5 text-[9px] font-bold text-slate-600 bg-slate-100 rounded">{{ strtoupper($r['status'] ?? 'DRAFT') }}</span>
                                @endif
                            </td>
                            <td class="py-2 px-3 border-r border-slate-200 text-center">
                                @if(($r['summary_condition'] ?? '') === 'good')
                                    <span class="font-bold text-[#008B5A]">✓ Baik</span>
                                @elseif(($r['summary_condition'] ?? '') === 'action_required')
                                    <span class="font-bold text-[#DC2626]">⚠ Perlu Tindakan</span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="py-2 px-3 text-center font-bold {{ ($r['total_findings'] ?? 0) > 0 ? 'text-[#DC2626]' : 'text-slate-400' }}">
                                {{ $r['total_findings'] ?? 0 }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-8 text-center text-slate-400 text-xs italic">
                                Tidak ada catatan inspeksi yang sesuai dengan kriteria filter aktif.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- FORMAL SIGN-OFF & APPROVAL BLOCK -->
        <div class="page-break-inside-avoid mt-8 pt-6 border-t-2 border-slate-800">
            <div class="text-xs text-slate-600 mb-4 font-medium italic">
                * Dokumen rekapitulasi ini dikeluarkan secara elektronik oleh Sistem Checklist Sarfas HSSE FT Maos dan sah sebagai laporan pemantauan berkala operasional HSSE.
            </div>

            <div class="grid grid-cols-3 gap-8 text-center text-xs">
                <!-- Sign 1: Inspector -->
                <div class="flex flex-col justify-between h-40 p-4 border border-slate-200 rounded-lg bg-slate-50/50">
                    <div>
                        <div class="font-bold text-slate-800">Dibuat / Dilaporkan Oleh:</div>
                        <div class="text-[11px] text-slate-500">Pemeriksa / Inspektor Sarfas</div>
                    </div>
                    <div>
                        <div class="font-bold text-slate-900 underline underline-offset-4">BAMBANG WIJAYA</div>
                        <div class="text-[10px] text-slate-500">Pekerja HSSE FT Maos</div>
                        <div class="text-[9px] text-slate-400 mt-1">Tanggal: {{ date('d/m/Y') }}</div>
                    </div>
                </div>

                <!-- Sign 2: Supervisor HSSE -->
                <div class="flex flex-col justify-between h-40 p-4 border border-slate-200 rounded-lg bg-slate-50/50">
                    <div>
                        <div class="font-bold text-slate-800">Diperiksa & Diverifikasi:</div>
                        <div class="text-[11px] text-slate-500">Supervisor HSSE</div>
                    </div>
                    <div>
                        <div class="font-bold text-slate-900 underline underline-offset-4">HENDRO PRASETYO</div>
                        <div class="text-[10px] text-slate-500">Sr. Spvr HSSE FT Maos</div>
                        <div class="text-[9px] text-slate-400 mt-1">Tanggal: {{ date('d/m/Y') }}</div>
                    </div>
                </div>

                <!-- Sign 3: FT Manager -->
                <div class="flex flex-col justify-between h-40 p-4 border border-slate-200 rounded-lg bg-slate-50/50">
                    <div>
                        <div class="font-bold text-slate-800">Mengetahui & Menyetujui:</div>
                        <div class="text-[11px] text-slate-500">Fuel Terminal Manager</div>
                    </div>
                    <div>
                        <div class="font-bold text-slate-900 underline underline-offset-4">IR. AHMAD FAUZI, S.T.</div>
                        <div class="text-[10px] text-slate-500">Fuel Terminal Manager Maos</div>
                        <div class="text-[9px] text-slate-400 mt-1">Tanggal: {{ date('d/m/Y') }}</div>
                    </div>
                </div>
            </div>
        </div>

    </div>

</body>
</html>
