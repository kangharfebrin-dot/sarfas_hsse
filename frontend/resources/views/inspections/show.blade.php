@extends('layouts.app')

@section('title', 'Hasil Inspeksi: ' . $inspection['inspection_no'])
@section('breadcrumb', 'Pemeriksaan / ' . $inspection['inspection_no'])
@section('page_title', 'Laporan Hasil Pemeriksaan Sarfas')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto" x-data="{ reviewModalOpen: false, reviewAction: 'APPROVE' }">

    <!-- Top Action Navigation -->
    <div class="flex items-center justify-between no-print">
        <a href="{{ route('inspections.index') }}" class="inline-flex items-center text-xs font-semibold text-slate-600 hover:text-slate-900">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Daftar Inspeksi
        </a>
        <div class="flex items-center space-x-2">
            <button onclick="window.print()" class="px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-lg shadow-xs transition flex items-center">
                <svg class="w-4 h-4 mr-1.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Cetak Lembar Laporan
            </button>

            <!-- Review Button for Approvers / Admin HSSE -->
            @if(in_array(session('auth_user.role_slug'), ['super_admin', 'admin_hsse', 'approver']) && $inspection['status'] === 'MENUNGGU_REVIEW')
                <button @click="reviewModalOpen = true" class="px-4 py-2 text-xs font-semibold text-white bg-[#00529B] hover:bg-[#003e75] rounded-lg shadow-xs transition flex items-center">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Verifikasi / Setujui Hasil
                </button>
            @endif
        </div>
    </div>

    <!-- Official Report Sheet Card -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 sm:p-8 space-y-6">
        
        <!-- Header Pertamina Official -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b-2 border-slate-800 gap-4">
            <div class="flex items-center space-x-3.5">
                <div class="w-12 h-12 rounded-xl bg-[#D71920] text-white flex items-center justify-center font-bold text-lg shrink-0">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-base font-bold text-slate-900 tracking-tight leading-tight">PT PERTAMINA PATRA NIAGA</h1>
                    <h2 class="text-xs font-semibold text-[#00529B]">FUEL TERMINAL MAOS — FUNGSI HSSE</h2>
                    <p class="text-[11px] text-slate-500">Berita Acara & Lembar Checklist Pemeriksaan Sarana & Fasilitas</p>
                </div>
            </div>

            <div class="text-left sm:text-right">
                <div class="text-xs font-mono font-bold text-[#D71920]">{{ $inspection['inspection_no'] }}</div>
                <div class="text-xs text-slate-600 font-medium mt-0.5">Tanggal: {{ date('d F Y', strtotime($inspection['inspection_date'])) }}</div>
                <div class="mt-1">
                    @if($inspection['status'] === 'SELESAI')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                            SELESAI DIVERIFIKASI
                        </span>
                    @elseif($inspection['status'] === 'MENUNGGU_REVIEW')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-100 text-blue-800 border border-blue-200">
                            MENUNGGU VERIFIKASI
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700">
                            DRAFT
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sarfas Specifications Info Box -->
        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
            <div>
                <span class="text-[10px] text-slate-400 block uppercase font-semibold">Peralatan / Sarfas</span>
                <span class="font-bold text-slate-900">{{ $inspection['sarfas_name'] }}</span>
                <span class="text-slate-500 font-mono block text-[11px]">{{ $inspection['asset_code'] }}</span>
            </div>
            <div>
                <span class="text-[10px] text-slate-400 block uppercase font-semibold">Lokasi Area</span>
                <span class="font-semibold text-slate-800">{{ $inspection['location_name'] }}</span>
                <span class="text-slate-500 block text-[11px]">{{ $inspection['category_name'] }}</span>
            </div>
            <div>
                <span class="text-[10px] text-slate-400 block uppercase font-semibold">Merek & Spesifikasi</span>
                <span class="font-semibold text-slate-800">{{ $inspection['brand_model'] ?? '-' }}</span>
                <span class="text-slate-500 block text-[11px]">{{ $inspection['capacity_specs'] ?? '-' }}</span>
            </div>
            <div>
                <span class="text-[10px] text-slate-400 block uppercase font-semibold">Kondisi Hasil</span>
                @if($inspection['summary_condition'] === 'BAIK')
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-bold bg-emerald-100 text-emerald-800">
                        KONDISI BAIK
                    </span>
                @elseif($inspection['summary_condition'] === 'KRITIS')
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-bold bg-red-100 text-red-800">
                        KONDISI KRITIS
                    </span>
                @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-bold bg-amber-100 text-amber-800">
                        PERLU TINDAK LANJUT
                    </span>
                @endif
            </div>
        </div>

        <!-- Inspection Items Result Table -->
        <div>
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-3">Rincian Hasil Checklist Pemeriksaan</h3>
            <div class="border border-slate-200 rounded-xl overflow-hidden">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-100/80 text-slate-700 font-bold uppercase text-[10px]">
                            <th class="p-3 w-16">Kode</th>
                            <th class="p-3">Item Pemeriksaan & Kriteria</th>
                            <th class="p-3 w-28 text-center">Status</th>
                            <th class="p-3">Keterangan / Catatan Temuan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @foreach($inspection['answers'] ?? [] as $ans)
                            <tr class="hover:bg-slate-50 transition {{ $ans['answer'] === 'TIDAK_BAIK' ? 'bg-red-50/20' : '' }}">
                                <td class="p-3 font-mono font-bold text-slate-800">{{ $ans['item_code'] }}</td>
                                <td class="p-3">
                                    <div class="font-medium text-slate-900">{{ $ans['question_text'] }}</div>
                                    @if($ans['is_critical'])
                                        <span class="text-[10px] font-bold text-red-600 block mt-0.5">Item Kritis Standar HSSE</span>
                                    @endif
                                </td>
                                <td class="p-3 text-center">
                                    @if($ans['answer'] === 'BAIK')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            BAIK
                                        </span>
                                    @elseif($ans['answer'] === 'TIDAK_BAIK')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-800 border border-red-200">
                                            TIDAK BAIK
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600">
                                            N/A
                                        </span>
                                    @endif
                                </td>
                                <td class="p-3 text-xs">
                                    @if($ans['notes'])
                                        <p class="font-medium text-slate-800">{{ $ans['notes'] }}</p>
                                        @if($ans['risk_level'])
                                            <span class="text-[10px] font-bold text-red-600">Risiko: {{ $ans['risk_level'] }}</span>
                                        @endif
                                    @else
                                        <span class="text-slate-400 italic">Sesuai standar operasional</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Generated Findings (if any) -->
        @if(count($inspection['findings'] ?? []) > 0)
            <div class="p-4 bg-red-50 border border-red-200 rounded-xl space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-bold text-red-900 uppercase tracking-wider flex items-center">
                        <svg class="w-4 h-4 mr-1.5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        Temuan Ketidaksesuaian Diterbitkan ({{ count($inspection['findings']) }} Temuan)
                    </h3>
                </div>
                <div class="space-y-2">
                    @foreach($inspection['findings'] as $f)
                        <div class="p-3 bg-white rounded-lg border border-red-200 flex items-center justify-between text-xs">
                            <div>
                                <span class="font-mono font-bold text-red-700">{{ $f['finding_no'] }}</span>
                                <span class="mx-1.5 text-slate-300">|</span>
                                <span class="font-semibold text-slate-800">{{ $f['title'] }}</span>
                                <p class="text-[11px] text-slate-500 mt-0.5">{{ $f['description'] }}</p>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-800">{{ $f['risk_level'] }}</span>
                                <a href="{{ route('findings.show', $f['id']) }}" class="block text-[11px] text-[#00529B] font-semibold hover:underline mt-1">Buka Temuan &rarr;</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- General Remarks -->
        @if($inspection['general_notes'])
            <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-lg text-xs space-y-1">
                <span class="font-bold text-slate-700 block">Catatan Umum Pemeriksa:</span>
                <p class="text-slate-600">{{ $inspection['general_notes'] }}</p>
            </div>
        @endif

        <!-- Signatures / Verification Stamp Block -->
        <div class="grid grid-cols-2 gap-8 pt-6 border-t border-slate-200 text-xs">
            <!-- Inspector Signature -->
            <div class="text-center p-4 rounded-xl bg-slate-50 border border-slate-200">
                <div class="text-[11px] text-slate-400 font-semibold uppercase">Dilaksanakan Oleh (Inspektor)</div>
                <div class="my-3 font-mono text-xs font-bold text-slate-800 bg-white py-2 px-3 border border-slate-200 rounded">
                    [ DIGITAL SIGNATURE VALID ]<br>
                    <span class="text-[10px] text-slate-500 font-sans">{{ $inspection['inspector_signature'] ?? 'Tercatat Sistem' }}</span>
                </div>
                <div class="font-bold text-slate-900">{{ $inspection['inspector_name'] }}</div>
                <div class="text-[10px] text-slate-500">Teknisi / Inspektor Lapangan HSSE</div>
            </div>

            <!-- Approver Verification Stamp -->
            <div class="text-center p-4 rounded-xl {{ $inspection['status'] === 'SELESAI' ? 'bg-emerald-50/50 border border-emerald-200' : 'bg-slate-50 border border-slate-200' }}">
                <div class="text-[11px] text-slate-400 font-semibold uppercase">Diverifikasi & Disetujui Oleh</div>
                @if($inspection['verified_by'])
                    <div class="my-3 font-mono text-xs font-bold text-emerald-800 bg-white py-2 px-3 border border-emerald-300 rounded shadow-2xs">
                        [ RESMI DISETUJUI ]<br>
                        <span class="text-[10px] text-emerald-600 font-sans">{{ date('d M Y H:i', strtotime($inspection['verified_at'])) }} WIB</span>
                    </div>
                    <div class="font-bold text-slate-900">{{ $inspection['verifier_name'] }}</div>
                    <div class="text-[10px] text-slate-500">Supervisor HSSE FT Maos</div>
                    @if($inspection['verification_notes'])
                        <p class="text-[10px] text-slate-500 mt-1 italic">"{{ $inspection['verification_notes'] }}"</p>
                    @endif
                @else
                    <div class="my-6 text-slate-400 italic">
                        Menunggu review dan verifikasi supervisor
                    </div>
                    <div class="font-medium text-slate-600">Supervisor / Officer HSSE</div>
                @endif
            </div>
        </div>

    </div>

    <!-- MODAL REVIEW INSPECTION (FOR APPROVER) -->
    <div x-show="reviewModalOpen" 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4" 
         x-cloak>
        <div @click.away="reviewModalOpen = false" class="bg-white rounded-xl shadow-xl border border-slate-200 max-w-md w-full p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                <h3 class="text-sm font-bold text-slate-900">Verifikasi Laporan Inspeksi</h3>
                <button @click="reviewModalOpen = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>

            <form action="{{ route('inspections.review', $inspection['id']) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Keputusan Verifikasi *</label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="flex items-center p-2.5 rounded-lg border border-slate-200 hover:bg-slate-50 cursor-pointer">
                            <input type="radio" name="action" value="APPROVE" x-model="reviewAction" checked class="text-emerald-600 focus:ring-emerald-500">
                            <span class="ml-2 text-xs font-bold text-emerald-800">Setujui & Selesaikan</span>
                        </label>
                        <label class="flex items-center p-2.5 rounded-lg border border-slate-200 hover:bg-slate-50 cursor-pointer">
                            <input type="radio" name="action" value="REJECT" x-model="reviewAction" class="text-red-600 focus:ring-red-500">
                            <span class="ml-2 text-xs font-bold text-red-800">Tolak / Revisi</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Verifikator</label>
                    <textarea name="verification_notes" rows="3" placeholder="Catatan pengesahan atau instruksi koreksi..."
                              class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#00529B] focus:outline-none"></textarea>
                </div>

                <div class="flex justify-end space-x-2 pt-3 border-t border-slate-200">
                    <button type="button" @click="reviewModalOpen = false" class="px-3 py-2 text-xs font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition">
                        Batal
                    </button>
                    <button type="submit" 
                            :class="reviewAction === 'APPROVE' ? 'bg-[#008B5A] hover:bg-[#007048]' : 'bg-[#D71920] hover:bg-[#b8141a]'"
                            class="px-4 py-2 text-xs font-semibold text-white rounded-lg shadow-xs transition">
                        Simpan Keputusan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
