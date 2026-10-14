@extends('layouts.app')

@section('title', 'Detail Temuan: ' . $finding['finding_no'])
@section('breadcrumb', 'Pemantauan / Temuan / ' . $finding['finding_no'])
@section('page_title', $finding['title'])

@section('content')
<div class="space-y-6 max-w-5xl mx-auto" x-data="{ actionModalOpen: false, verifyModalOpen: false, verifyDecision: 'VERIFY_CLOSE' }">

    <!-- Top Action Navigation -->
    <div class="flex items-center justify-between">
        <a href="{{ route('findings.index') }}" class="inline-flex items-center text-xs font-semibold text-slate-600 hover:text-slate-900">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Daftar Temuan
        </a>

        <div class="flex items-center space-x-2">
            <!-- Update Action Progress (Available when not closed) -->
            @if($finding['status'] !== 'SELESAI')
                <button @click="actionModalOpen = true" class="px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-lg shadow-xs transition flex items-center">
                    <svg class="w-4 h-4 mr-1.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Perbarui Progres Tindakan
                </button>
            @endif

            <!-- Verify / Close Button (For Approver or Admin HSSE) -->
            @if(in_array(session('auth_user.role_slug'), ['super_admin', 'admin_hsse', 'approver']) && $finding['status'] !== 'SELESAI')
                <button @click="verifyModalOpen = true" class="px-4 py-2 text-xs font-semibold text-white bg-[#008B5A] hover:bg-[#007048] rounded-lg shadow-xs transition flex items-center">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Verifikasi Penutupan Temuan
                </button>
            @endif
        </div>
    </div>

    <!-- Main Finding Info Card -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-xs p-6 space-y-6">
        
        <div class="flex flex-col sm:flex-row sm:items-start justify-between pb-4 border-b border-slate-100 gap-3">
            <div>
                <div class="flex items-center space-x-2">
                    <span class="text-xs font-mono font-bold text-slate-900 px-2 py-0.5 bg-slate-100 rounded border border-slate-200">
                        {{ $finding['finding_no'] }}
                    </span>
                    @if($finding['risk_level'] === 'KRITIS')
                        <span class="px-2.5 py-0.5 rounded text-[11px] font-bold bg-red-100 text-red-800 border border-red-200 flex items-center">
                            <span class="w-2 h-2 rounded-full bg-red-600 mr-1.5"></span> RISIKO KRITIS
                        </span>
                    @elseif($finding['risk_level'] === 'TINGGI')
                        <span class="px-2.5 py-0.5 rounded text-[11px] font-bold bg-orange-100 text-orange-800 border border-orange-200">
                            RISIKO TINGGI
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                            RISIKO SEDANG
                        </span>
                    @endif
                </div>

                <h2 class="text-base font-bold text-slate-900 mt-2">{{ $finding['title'] }}</h2>
                <p class="text-xs text-slate-500 mt-1">
                    Sarfas: <a href="{{ route('sarfas.show', $finding['sarfas_id']) }}" class="font-bold text-[#00529B] hover:underline">{{ $finding['sarfas_name'] }} ({{ $finding['asset_code'] }})</a> · 
                    Lokasi: <span class="font-semibold text-slate-700">{{ $finding['location_name'] }}</span>
                </p>
            </div>

            <div>
                @if($finding['status'] === 'SELESAI')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                        <span class="w-2 h-2 rounded-full bg-emerald-600 mr-2"></span> SELESAI & DITUTUP
                    </span>
                @elseif($finding['status'] === 'MENUNGGU_VERIFIKASI')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-200">
                        <span class="w-2 h-2 rounded-full bg-blue-600 mr-2"></span> MENUNGGU VERIFIKASI
                    </span>
                @elseif($finding['status'] === 'DALAM_TINDAK_LANJUT')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                        <span class="w-2 h-2 rounded-full bg-amber-600 mr-2"></span> DALAM PENANGANAN
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800 border border-red-200">
                        <span class="w-2 h-2 rounded-full bg-red-600 mr-2"></span> TEMUAN BARU
                    </span>
                @endif
            </div>
        </div>

        <!-- Meta Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
            <div class="p-3 bg-slate-50 rounded-lg border border-slate-100">
                <span class="text-[10px] text-slate-400 block font-semibold uppercase">Pelapor Temuan</span>
                <span class="font-bold text-slate-800">{{ $finding['reported_by_name'] }}</span>
                <span class="text-[10px] text-slate-400 block">{{ date('d M Y', strtotime($finding['created_at'])) }}</span>
            </div>
            <div class="p-3 bg-slate-50 rounded-lg border border-slate-100">
                <span class="text-[10px] text-slate-400 block font-semibold uppercase">Penanggung Jawab (PIC)</span>
                <span class="font-bold text-slate-800">{{ $finding['assigned_to_name'] ?? 'Belum Ditugaskan' }}</span>
                <span class="text-[10px] text-slate-500 block">Unit Pemeliharaan / HSSE</span>
            </div>
            <div class="p-3 bg-slate-50 rounded-lg border border-slate-100">
                <span class="text-[10px] text-slate-400 block font-semibold uppercase">Target Penyelesaian (SLA)</span>
                <span class="font-bold text-[#D71920]">{{ $finding['due_date'] ? date('d M Y', strtotime($finding['due_date'])) : '-' }}</span>
                <span class="text-[10px] text-slate-400 block">Batas perbaikan fisik</span>
            </div>
            <div class="p-3 bg-slate-50 rounded-lg border border-slate-100">
                <span class="text-[10px] text-slate-400 block font-semibold uppercase">Status Verifikasi</span>
                @if($finding['verified_by'])
                    <span class="font-bold text-emerald-700">Diverifikasi oleh {{ $finding['verified_by_name'] }}</span>
                    <span class="text-[10px] text-slate-400 block">{{ date('d M Y', strtotime($finding['verified_at'])) }}</span>
                @else
                    <span class="text-slate-500 font-medium italic">Belum diverifikasi</span>
                @endif
            </div>
        </div>

        <!-- Description Box -->
        <div class="p-4 bg-slate-50/80 rounded-xl border border-slate-200 text-xs space-y-1.5">
            <span class="font-bold text-slate-800 block">Uraian Detail Ketidaksesuaian:</span>
            <p class="text-slate-700 leading-relaxed">{{ $finding['description'] }}</p>
            @if($finding['item_code'])
                <p class="text-[11px] text-slate-500 mt-1 italic">Item Checklist Terkait: [{{ $finding['item_code'] }}] {{ $finding['question_text'] }}</p>
            @endif
        </div>

        <!-- Before & After Photos Side-by-Side -->
        <div>
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-3">Dokumentasi Bukti Fisik Lapangan</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Foto Sebelum -->
                <div class="p-4 rounded-xl border border-red-200 bg-red-50/20 text-center space-y-2">
                    <div class="text-xs font-bold text-red-700 uppercase">Foto Kondisi Temuan (Sebelum)</div>
                    <div class="h-44 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center overflow-hidden">
                        <div class="text-center p-4">
                            <svg class="w-10 h-10 text-red-400 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span class="text-xs font-semibold text-slate-700">Bukti Fisik Temuan Tercatat</span>
                            <span class="text-[10px] text-slate-400 block mt-0.5">ID: {{ $finding['finding_no'] }}-BEFORE</span>
                        </div>
                    </div>
                    <span class="text-[10px] text-slate-500 italic block">Diunggah saat inspeksi awal</span>
                </div>

                <!-- Foto Sesudah -->
                <div class="p-4 rounded-xl border {{ $finding['status'] === 'SELESAI' ? 'border-emerald-200 bg-emerald-50/20' : 'border-slate-200 bg-slate-50/30' }} text-center space-y-2">
                    <div class="text-xs font-bold {{ $finding['status'] === 'SELESAI' ? 'text-emerald-700' : 'text-slate-600' }} uppercase">
                        Foto Hasil Tindak Lanjut (Sesudah Perbaikan)
                    </div>
                    <div class="h-44 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center overflow-hidden">
                        @if($finding['photo_after'] || $finding['status'] === 'SELESAI')
                            <div class="text-center p-4">
                                <svg class="w-10 h-10 text-emerald-500 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="text-xs font-bold text-emerald-800">Bukti Perbaikan Tervalidasi</span>
                                <span class="text-[10px] text-slate-500 block mt-0.5">Kondisi fisik kembali memenuhi standar</span>
                            </div>
                        @else
                            <div class="text-center p-4 text-slate-400">
                                <svg class="w-8 h-8 mx-auto mb-1 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                </svg>
                                <span class="text-xs font-medium">Belum ada foto perbaikan</span>
                                <span class="text-[10px] text-slate-400 block mt-0.5">Unggah bukti setelah perbaikan selesai</span>
                            </div>
                        @endif
                    </div>
                    <span class="text-[10px] text-slate-500 italic block">Wajib sebelum permohonan verifikasi</span>
                </div>
            </div>
        </div>

        <!-- Rencana Tindakan & Catatan Korektif -->
        @if($finding['corrective_action_plan'] || $finding['corrective_action_notes'])
            <div class="p-4 bg-blue-50/40 rounded-xl border border-blue-200 text-xs space-y-2">
                @if($finding['corrective_action_plan'])
                    <div>
                        <span class="font-bold text-slate-800 block">Rencana Tindakan Korektif (Action Plan):</span>
                        <p class="text-slate-700 mt-0.5">{{ $finding['corrective_action_plan'] }}</p>
                    </div>
                @endif
                @if($finding['corrective_action_notes'])
                    <div class="pt-2 border-t border-blue-100">
                        <span class="font-bold text-slate-800 block">Catatan Progres Lapangan:</span>
                        <p class="text-slate-700 mt-0.5">{{ $finding['corrective_action_notes'] }}</p>
                    </div>
                @endif
            </div>
        @endif

        <!-- Timeline Riwayat Tindakan (Action History) -->
        <div>
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-3">Riwayat Log Tindak Lanjut & Validasi</h3>
            <div class="border border-slate-200 rounded-xl divide-y divide-slate-100 overflow-hidden">
                @forelse($finding['timeline'] ?? [] as $act)
                    <div class="p-4 flex items-start space-x-3 text-xs hover:bg-slate-50 transition">
                        <div class="w-2.5 h-2.5 rounded-full mt-1 shrink-0
                            {{ $act['action_type'] === 'VERIFIED_CLOSED' ? 'bg-emerald-500' : ($act['action_type'] === 'CREATED' ? 'bg-red-500' : 'bg-blue-500') }}">
                        </div>
                        <div class="flex-1 space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-900">{{ $act['user_name'] }} ({{ $act['jabatan'] ?? 'Staff' }})</span>
                                <span class="text-[10px] text-slate-400">{{ date('d M Y H:i', strtotime($act['created_at'])) }} WIB</span>
                            </div>
                            <span class="inline-block px-1.5 py-0.2 text-[9px] font-bold rounded bg-slate-100 text-slate-600 font-mono">
                                {{ $act['action_type'] }}
                            </span>
                            <p class="text-slate-600 mt-1">{{ $act['notes'] }}</p>
                        </div>
                    </div>
                @empty
                    <div class="p-4 text-center text-xs text-slate-400">Belum ada catatan riwayat tindakan.</div>
                @endforelse
            </div>
        </div>

    </div>

    <!-- MODAL UPDATE PROGRESS / ACTION -->
    <div x-show="actionModalOpen" 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4" 
         x-cloak>
        <div @click.away="actionModalOpen = false" class="bg-white rounded-xl shadow-xl border border-slate-200 max-w-lg w-full p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                <h3 class="text-sm font-bold text-slate-900">Perbarui Tindakan: {{ $finding['finding_no'] }}</h3>
                <button @click="actionModalOpen = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>

            <form action="{{ route('findings.updateAction', $finding['id']) }}" method="POST" class="space-y-3.5">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Status Progres *</label>
                    <select name="status" required class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none bg-white">
                        <option value="DALAM_TINDAK_LANJUT" {{ $finding['status'] === 'DALAM_TINDAK_LANJUT' ? 'selected' : '' }}>Dalam Tindak Lanjut (Sedang Dikerjakan)</option>
                        <option value="MENUNGGU_VERIFIKASI" {{ $finding['status'] === 'MENUNGGU_VERIFIKASI' ? 'selected' : '' }}>Menunggu Verifikasi (Selesai Dikerjakan, Siap Dicek)</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Penanggung Jawab (PIC)</label>
                        <select name="assigned_to" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none bg-white">
                            <option value="">Pilih Petugas PIC</option>
                            @foreach($users as $u)
                                <option value="{{ $u['id'] }}" {{ $finding['assigned_to'] == $u['id'] ? 'selected' : '' }}>
                                    {{ $u['name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Target Tenggat Waktu</label>
                        <input type="date" name="due_date" value="{{ $finding['due_date'] }}"
                               class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Rencana Tindakan Korektif</label>
                    <textarea name="corrective_action_plan" rows="2" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none">{{ $finding['corrective_action_plan'] }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Tindakan Lapangan / Bukti</label>
                    <textarea name="corrective_action_notes" rows="2" placeholder="Uraikan suku cadang yang diganti, perbaikan fisik yang dilakukan..."
                              class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none">{{ $finding['corrective_action_notes'] }}</textarea>
                </div>

                <!-- Mockup Foto Sesudah -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Simulasi Upload Foto Bukti Perbaikan</label>
                    <input type="text" name="photo_after" value="/assets/images/demo/repaired_proof.jpg"
                           class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg bg-slate-50 font-mono text-slate-500">
                </div>

                <div class="flex justify-end space-x-2 pt-3 border-t border-slate-200">
                    <button type="button" @click="actionModalOpen = false" class="px-3 py-2 text-xs font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-[#00529B] hover:bg-[#003e75] rounded-lg transition">
                        Simpan Pembaruan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL VERIFIKASI PENUTUPAN TEMUAN (APPROVER) -->
    <div x-show="verifyModalOpen" 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4" 
         x-cloak>
        <div @click.away="verifyModalOpen = false" class="bg-white rounded-xl shadow-xl border border-slate-200 max-w-md w-full p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                <h3 class="text-sm font-bold text-slate-900">Verifikasi Penutupan Temuan: {{ $finding['finding_no'] }}</h3>
                <button @click="verifyModalOpen = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>

            <p class="text-xs text-slate-500">
                Sebagai verifikator HSSE berwenang, pastikan perbaikan fisik di lapangan telah dicek dan memenuhi standar keselamatan kerja sebelum menutup temuan.
            </p>

            <form action="{{ route('findings.verifyClose', $finding['id']) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Keputusan Pengawas *</label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="flex items-center p-2.5 rounded-lg border border-slate-200 hover:bg-slate-50 cursor-pointer">
                            <input type="radio" name="action" value="VERIFY_CLOSE" x-model="verifyDecision" checked class="text-emerald-600 focus:ring-emerald-500">
                            <span class="ml-2 text-xs font-bold text-emerald-800">Setujui & Tutup Temuan</span>
                        </label>
                        <label class="flex items-center p-2.5 rounded-lg border border-slate-200 hover:bg-slate-50 cursor-pointer">
                            <input type="radio" name="action" value="REJECT" x-model="verifyDecision" class="text-red-600 focus:ring-red-500">
                            <span class="ml-2 text-xs font-bold text-red-800">Tolak & Minta Perbaikan Ulang</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Pengawas Verifikasi *</label>
                    <textarea name="verification_notes" rows="3" required placeholder="Tuliskan hasil pengecekan fisik di lapangan..."
                              class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#008B5A] focus:outline-none"></textarea>
                </div>

                <div class="flex justify-end space-x-2 pt-3 border-t border-slate-200">
                    <button type="button" @click="verifyModalOpen = false" class="px-3 py-2 text-xs font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition">
                        Batal
                    </button>
                    <button type="submit" 
                            :class="verifyDecision === 'VERIFY_CLOSE' ? 'bg-[#008B5A] hover:bg-[#007048]' : 'bg-[#D71920] hover:bg-[#b8141a]'"
                            class="px-4 py-2 text-xs font-semibold text-white rounded-lg shadow-xs transition">
                        Konfirmasi Keputusan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
