@extends('layouts.app')

@section('title', 'Detail Sarfas - ' . $sarfas['asset_code'])
@section('breadcrumb', 'Master Data / Sarfas / ' . $sarfas['asset_code'])
@section('page_title', $sarfas['name'])

@section('content')
<div class="space-y-6" x-data="{ editModalOpen: false }">

    <!-- Top Action Bar -->
    <div class="flex items-center justify-between">
        <a href="{{ route('sarfas.index') }}" class="inline-flex items-center text-xs font-semibold text-slate-600 hover:text-slate-900">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Daftar Sarfas
        </a>
        <div class="flex items-center space-x-2">
            <a href="{{ route('inspections.create', ['sarfas_id' => $sarfas['id']]) }}" class="px-3.5 py-2 text-xs font-semibold text-white bg-[#008B5A] hover:bg-[#007048] rounded-lg shadow-xs transition flex items-center">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Lakukan Inspeksi Sekarang
            </a>
            @if(in_array(session('auth_user.role_slug'), ['super_admin', 'admin_hsse']))
                <button @click="editModalOpen = true" class="px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-lg shadow-xs transition flex items-center">
                    <svg class="w-4 h-4 mr-1.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Sunting Data Sarfas
                </button>
            @endif
        </div>
    </div>

    <!-- Sarfas Main Card & Specification Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Left: Specifications -->
        <div class="lg:col-span-8 bg-white border border-slate-200 rounded-xl shadow-xs p-6 space-y-6">
            <div class="flex items-start justify-between pb-4 border-b border-slate-100">
                <div>
                    <span class="text-xs font-mono font-bold text-[#D71920] px-2 py-0.5 bg-red-50 rounded border border-red-200">
                        {{ $sarfas['asset_code'] }}
                    </span>
                    <h2 class="text-lg font-bold text-slate-900 mt-2">{{ $sarfas['name'] }}</h2>
                    <p class="text-xs text-slate-500 mt-1">Kategori: <span class="font-semibold text-slate-700">{{ $sarfas['category_name'] }}</span></p>
                </div>
                <div>
                    @if($sarfas['condition_status'] === 'BAIK')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                            <span class="w-2 h-2 rounded-full bg-emerald-600 mr-2"></span> KONDISI BAIK
                        </span>
                    @elseif($sarfas['condition_status'] === 'PERLU_PERHATIAN')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                            <span class="w-2 h-2 rounded-full bg-amber-600 mr-2"></span> PERLU PERHATIAN
                        </span>
                    @elseif($sarfas['condition_status'] === 'RUSAK_RINGAN')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-orange-100 text-orange-800 border border-orange-200">
                            <span class="w-2 h-2 rounded-full bg-orange-600 mr-2"></span> RUSAK RINGAN
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800 border border-red-200">
                            <span class="w-2 h-2 rounded-full bg-red-600 mr-2"></span> RUSAK BERAT
                        </span>
                    @endif
                </div>
            </div>

            <!-- Specs Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-xs">
                <div class="p-3 rounded-lg bg-slate-50 border border-slate-100">
                    <div class="text-[11px] text-slate-500">Lokasi / Area</div>
                    <div class="font-semibold text-slate-800 mt-0.5">{{ $sarfas['location_name'] }} ({{ $sarfas['location_code'] }})</div>
                </div>
                <div class="p-3 rounded-lg bg-slate-50 border border-slate-100">
                    <div class="text-[11px] text-slate-500">Merek & Model</div>
                    <div class="font-semibold text-slate-800 mt-0.5">{{ $sarfas['brand_model'] ?? '-' }}</div>
                </div>
                <div class="p-3 rounded-lg bg-slate-50 border border-slate-100">
                    <div class="text-[11px] text-slate-500">Nomor Seri Pabrik</div>
                    <div class="font-semibold text-slate-800 mt-0.5 font-mono">{{ $sarfas['serial_number'] ?? '-' }}</div>
                </div>
                <div class="p-3 rounded-lg bg-slate-50 border border-slate-100">
                    <div class="text-[11px] text-slate-500">Kapasitas / Tekanan Kerja</div>
                    <div class="font-semibold text-slate-800 mt-0.5">{{ $sarfas['capacity_specs'] ?? '-' }}</div>
                </div>
                <div class="p-3 rounded-lg bg-slate-50 border border-slate-100">
                    <div class="text-[11px] text-slate-500">Tanggal Instalasi</div>
                    <div class="font-semibold text-slate-800 mt-0.5">{{ $sarfas['installation_date'] ? date('d M Y', strtotime($sarfas['installation_date'])) : '-' }}</div>
                </div>
                <div class="p-3 rounded-lg bg-slate-50 border border-slate-100">
                    <div class="text-[11px] text-slate-500">Status Operasional</div>
                    <div class="font-semibold text-emerald-600 mt-0.5">Aktif Terdaftar</div>
                </div>
            </div>

            <!-- Notes Section -->
            @if($sarfas['notes'])
                <div class="p-3.5 bg-blue-50/50 border border-blue-100 rounded-lg text-xs">
                    <div class="font-semibold text-slate-700">Catatan Lokasi & Penempatan:</div>
                    <p class="text-slate-600 mt-1">{{ $sarfas['notes'] }}</p>
                </div>
            @endif
        </div>

        <!-- Right: QR Barcode & Inspection Schedule -->
        <div class="lg:col-span-4 bg-white border border-slate-200 rounded-xl shadow-xs p-6 space-y-5">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">Identifikasi Fisik & QR Tag</h3>
            
            <!-- Mockup QR Code -->
            <div class="p-4 border-2 border-dashed border-slate-200 rounded-xl text-center bg-slate-50 flex flex-col items-center">
                <div class="w-32 h-32 bg-white p-2 border border-slate-200 rounded-lg shadow-xs flex items-center justify-center">
                    <!-- SVG QR Representation -->
                    <svg class="w-28 h-28 text-slate-800" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M2 2h8v8H2V2zm2 2v4h4V4H4zm10-2h8v8h-8V2zm2 2v4h4V4h-4zM2 14h8v8H2v-8zm2 2v4h4v-4H4zm14-2h4v2h-4v-2zm-4 0h2v4h-2v-4zm2 4h4v4h-4v-4zm2-2h2v2h-2v-2zm-4 4h2v2h-2v-2zm-6-2h2v2h-2v-2zm0-4h2v2h-2v-2zm2 2h2v2h-2v-2z" />
                    </svg>
                </div>
                <div class="mt-2 text-xs font-mono font-bold text-slate-800">{{ $sarfas['asset_code'] }}</div>
                <div class="text-[10px] text-slate-400">Scan QR di lapangan untuk inspeksi cepat</div>
            </div>

            <!-- Next Due Date -->
            <div class="p-3.5 rounded-lg border border-slate-200 bg-slate-50/50 space-y-2">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-500">Pemeriksaan Terakhir:</span>
                    <span class="font-semibold text-slate-800">{{ $sarfas['last_inspection_date'] ? date('d M Y', strtotime($sarfas['last_inspection_date'])) : '-' }}</span>
                </div>
                <div class="flex items-center justify-between text-xs pt-2 border-t border-slate-200">
                    <span class="text-slate-500">Jadwal Berikutnya:</span>
                    <span class="font-bold text-[#D71920]">{{ $sarfas['next_inspection_date'] ? date('d M Y', strtotime($sarfas['next_inspection_date'])) : '-' }}</span>
                </div>
            </div>
        </div>

    </div>

    <!-- Riwayat Inspeksi & Riwayat Temuan Tabs / Lists -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Riwayat Inspeksi -->
        <div class="lg:col-span-6 bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-200 flex items-center justify-between bg-slate-50/50">
                <h3 class="text-sm font-bold text-slate-800">Riwayat Pemeriksaan Sarfas</h3>
                <span class="text-xs text-slate-500">{{ count($sarfas['inspection_history'] ?? []) }} Catatan</span>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($sarfas['inspection_history'] as $insp)
                    <div class="p-3.5 hover:bg-slate-50 transition flex items-center justify-between">
                        <div>
                            <div class="text-xs font-bold text-slate-900">{{ $insp['inspection_no'] }}</div>
                            <div class="text-[11px] text-slate-500">{{ $insp['template_title'] }}</div>
                            <div class="text-[10px] text-slate-400 mt-0.5">Pemeriksa: {{ $insp['inspector_name'] }} · {{ date('d M Y', strtotime($insp['inspection_date'])) }}</div>
                        </div>
                        <div class="text-right">
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded {{ $insp['summary_condition'] === 'BAIK' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                                {{ $insp['summary_condition'] }}
                            </span>
                            <div class="mt-1">
                                <a href="{{ route('inspections.show', $insp['id']) }}" class="text-[11px] font-semibold text-[#00529B] hover:underline">Lihat Hasil &rarr;</a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center text-xs text-slate-400">Belum ada riwayat inspeksi untuk sarfas ini.</div>
                @endforelse
            </div>
        </div>

        <!-- Riwayat Temuan -->
        <div class="lg:col-span-6 bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-200 flex items-center justify-between bg-slate-50/50">
                <h3 class="text-sm font-bold text-slate-800">Riwayat Temuan Ketidaksesuaian</h3>
                <span class="text-xs text-slate-500">{{ count($sarfas['findings_history'] ?? []) }} Temuan</span>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($sarfas['findings_history'] as $find)
                    <div class="p-3.5 hover:bg-slate-50 transition flex items-center justify-between">
                        <div>
                            <div class="flex items-center space-x-2">
                                <span class="text-xs font-bold text-slate-900">{{ $find['finding_no'] }}</span>
                                <span class="px-1.5 py-0.2 text-[9px] font-bold rounded {{ $find['risk_level'] === 'KRITIS' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ $find['risk_level'] }}
                                </span>
                            </div>
                            <div class="text-xs text-slate-800 font-medium mt-0.5">{{ $find['title'] }}</div>
                            <div class="text-[10px] text-slate-400">PIC: {{ $find['assigned_to_name'] ?? 'Belum Ditugaskan' }}</div>
                        </div>
                        <div class="text-right">
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded {{ $find['status'] === 'SELESAI' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ $find['status'] }}
                            </span>
                            <div class="mt-1">
                                <a href="{{ route('findings.show', $find['id']) }}" class="text-[11px] font-semibold text-[#00529B] hover:underline">Detail &rarr;</a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center text-xs text-slate-400">Tidak ada riwayat temuan pada sarfas ini.</div>
                @endforelse
            </div>
        </div>

    </div>

    <!-- MODAL EDIT SARFAS -->
    <div x-show="editModalOpen" 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4" 
         x-cloak>
        <div @click.away="editModalOpen = false" class="bg-white rounded-xl shadow-xl border border-slate-200 max-w-lg w-full p-6">
            <div class="flex items-center justify-between pb-3 border-b border-slate-200 mb-4">
                <h3 class="text-sm font-bold text-slate-900">Perbarui Data Sarfas: {{ $sarfas['asset_code'] }}</h3>
                <button @click="editModalOpen = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>

            <form action="{{ route('sarfas.update', $sarfas['id']) }}" method="POST" class="space-y-3.5">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Sarfas / Peralatan *</label>
                    <input type="text" name="name" value="{{ $sarfas['name'] }}" required
                           class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Kategori *</label>
                        <select name="category_id" required class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none">
                            @foreach($categories as $cat)
                                <option value="{{ $cat['id'] }}" {{ $sarfas['category_id'] == $cat['id'] ? 'selected' : '' }}>{{ $cat['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Lokasi Area *</label>
                        <select name="location_id" required class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none">
                            @foreach($locations as $loc)
                                <option value="{{ $loc['id'] }}" {{ $sarfas['location_id'] == $loc['id'] ? 'selected' : '' }}>{{ $loc['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Status Kondisi</label>
                        <select name="condition_status" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none">
                            <option value="BAIK" {{ $sarfas['condition_status'] === 'BAIK' ? 'selected' : '' }}>Baik</option>
                            <option value="PERLU_PERHATIAN" {{ $sarfas['condition_status'] === 'PERLU_PERHATIAN' ? 'selected' : '' }}>Perlu Perhatian</option>
                            <option value="RUSAK_RINGAN" {{ $sarfas['condition_status'] === 'RUSAK_RINGAN' ? 'selected' : '' }}>Rusak Ringan</option>
                            <option value="RUSAK_BERAT" {{ $sarfas['condition_status'] === 'RUSAK_BERAT' ? 'selected' : '' }}>Rusak Berat</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Seri</label>
                        <input type="text" name="serial_number" value="{{ $sarfas['serial_number'] }}"
                               class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Merek / Model</label>
                        <input type="text" name="brand_model" value="{{ $sarfas['brand_model'] }}"
                               class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Kapasitas / Spesifikasi</label>
                        <input type="text" name="capacity_specs" value="{{ $sarfas['capacity_specs'] }}"
                               class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan</label>
                    <textarea name="notes" rows="2" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none">{{ $sarfas['notes'] }}</textarea>
                </div>

                <div class="flex justify-end space-x-2 pt-3 border-t border-slate-200">
                    <button type="button" @click="editModalOpen = false" class="px-3 py-2 text-xs font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-[#00529B] hover:bg-[#003e75] rounded-lg transition">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
