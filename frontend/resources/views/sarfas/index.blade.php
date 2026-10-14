@extends('layouts.app')

@section('title', 'Data Sarana & Fasilitas (Sarfas)')
@section('breadcrumb', 'Master Data / Sarfas HSSE')
@section('page_title', 'Inventaris Sarfas HSSE FT Maos')

@section('content')
<div class="space-y-6" x-data="{ addModalOpen: false }">

    <!-- Header Actions & Search -->
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <!-- Search and Filter Form -->
        <form method="GET" action="{{ route('sarfas.index') }}" class="flex-1 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5">
            <div>
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Cari kode atau nama sarfas..." 
                       class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none">
            </div>
            <div>
                <select name="category_id" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none">
                    <option value="">-- Semua Kategori --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat['id'] }}" {{ ($filters['category_id'] ?? '') == $cat['id'] ? 'selected' : '' }}>
                            {{ $cat['name'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="location_id" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none">
                    <option value="">-- Semua Lokasi --</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc['id'] }}" {{ ($filters['location_id'] ?? '') == $loc['id'] ? 'selected' : '' }}>
                            {{ $loc['name'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex space-x-2">
                <select name="condition_status" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none">
                    <option value="">-- Kondisi --</option>
                    <option value="BAIK" {{ ($filters['condition_status'] ?? '') == 'BAIK' ? 'selected' : '' }}>Baik</option>
                    <option value="PERLU_PERHATIAN" {{ ($filters['condition_status'] ?? '') == 'PERLU_PERHATIAN' ? 'selected' : '' }}>Perlu Perhatian</option>
                    <option value="RUSAK_RINGAN" {{ ($filters['condition_status'] ?? '') == 'RUSAK_RINGAN' ? 'selected' : '' }}>Rusak Ringan</option>
                    <option value="RUSAK_BERAT" {{ ($filters['condition_status'] ?? '') == 'RUSAK_BERAT' ? 'selected' : '' }}>Rusak Berat</option>
                </select>
                <button type="submit" class="px-3.5 py-2 text-xs font-semibold text-white bg-[#00529B] hover:bg-[#003e75] rounded-lg transition shrink-0">
                    Filter
                </button>
            </div>
        </form>

        <!-- Add Sarfas Button (Admin Only) -->
        @if(in_array(session('auth_user.role_slug'), ['super_admin', 'admin_hsse']))
            <button @click="addModalOpen = true" class="px-4 py-2 text-xs font-semibold text-white bg-[#D71920] hover:bg-[#b8141a] rounded-lg shadow-xs transition flex items-center shrink-0 justify-center">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Sarfas Baru
            </button>
        @endif
    </div>

    <!-- Sarfas Table -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50/80 text-slate-700 font-bold uppercase tracking-wider text-[10px]">
                        <th class="p-3.5">Kode Sarfas</th>
                        <th class="p-3.5">Nama Peralatan & Spesifikasi</th>
                        <th class="p-3.5">Kategori</th>
                        <th class="p-3.5">Lokasi / Area</th>
                        <th class="p-3.5">Pemeriksaan Terakhir</th>
                        <th class="p-3.5">Kondisi</th>
                        <th class="p-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($sarfasList as $item)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="p-3.5 font-mono font-bold text-slate-900">
                                {{ $item['asset_code'] }}
                            </td>
                            <td class="p-3.5">
                                <div class="font-semibold text-slate-900">{{ $item['name'] }}</div>
                                <div class="text-[11px] text-slate-500">{{ $item['brand_model'] ?? '-' }} · {{ $item['capacity_specs'] ?? '-' }}</div>
                            </td>
                            <td class="p-3.5 text-slate-700 font-medium">
                                {{ $item['category_name'] }}
                            </td>
                            <td class="p-3.5">
                                <div class="text-slate-800 font-medium">{{ $item['location_name'] }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $item['location_code'] }}</div>
                            </td>
                            <td class="p-3.5 text-slate-600">
                                @if($item['last_inspection_date'])
                                    <div>{{ date('d M Y', strtotime($item['last_inspection_date'])) }}</div>
                                    <div class="text-[10px] text-slate-400">Next: {{ $item['next_inspection_date'] ? date('d M Y', strtotime($item['next_inspection_date'])) : '-' }}</div>
                                @else
                                    <span class="text-amber-600 font-medium">Belum pernah diinspeksi</span>
                                @endif
                            </td>
                            <td class="p-3.5">
                                @if($item['condition_status'] === 'BAIK')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 mr-1.5"></span> BAIK
                                    </span>
                                @elseif($item['condition_status'] === 'PERLU_PERHATIAN')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-600 mr-1.5"></span> PERLU PERHATIAN
                                    </span>
                                @elseif($item['condition_status'] === 'RUSAK_RINGAN')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-orange-100 text-orange-800 border border-orange-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-orange-600 mr-1.5"></span> RUSAK RINGAN
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-800 border border-red-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-600 mr-1.5"></span> RUSAK BERAT
                                    </span>
                                @endif
                            </td>
                            <td class="p-3.5 text-right space-x-2">
                                <a href="{{ route('inspections.create', ['sarfas_id' => $item['id']]) }}" class="inline-flex items-center px-2.5 py-1 text-xs font-semibold text-white bg-[#008B5A] hover:bg-[#007048] rounded transition" title="Inspeksi Sarfas Ini">
                                    Inspeksi
                                </a>
                                <a href="{{ route('sarfas.show', $item['id']) }}" class="inline-flex items-center px-2.5 py-1 text-xs font-semibold text-[#00529B] bg-blue-50 hover:bg-blue-100 rounded transition">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-400">
                                <svg class="w-8 h-8 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Tidak ada data sarfas yang sesuai dengan kriteria pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL TAMBAH SARFAS -->
    <div x-show="addModalOpen" 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4" 
         x-cloak>
        <div @click.away="addModalOpen = false" class="bg-white rounded-xl shadow-xl border border-slate-200 max-w-lg w-full p-6">
            <div class="flex items-center justify-between pb-3 border-b border-slate-200 mb-4">
                <h3 class="text-sm font-bold text-slate-900">Registrasi Sarfas HSSE Baru</h3>
                <button @click="addModalOpen = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>

            <form action="{{ route('sarfas.store') }}" method="POST" class="space-y-3.5">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Kode Sarfas *</label>
                        <input type="text" name="asset_code" placeholder="Misal: APAR-TT-005" required
                               class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Kategori *</label>
                        <select name="category_id" required class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none">
                            <option value="">Pilih Kategori</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat['id'] }}">{{ $cat['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Sarfas / Peralatan *</label>
                    <input type="text" name="name" placeholder="Misal: APAR DCP 9 Kg Tangki Timbun T-02" required
                           class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Lokasi Area *</label>
                        <select name="location_id" required class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none">
                            <option value="">Pilih Lokasi</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc['id'] }}">{{ $loc['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Status Awal</label>
                        <select name="condition_status" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none">
                            <option value="BAIK">Baik</option>
                            <option value="PERLU_PERHATIAN">Perlu Perhatian</option>
                            <option value="RUSAK_RINGAN">Rusak Ringan</option>
                            <option value="RUSAK_BERAT">Rusak Berat</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Merek / Model</label>
                        <input type="text" name="brand_model" placeholder="Misal: Yamato 9 Kg"
                               class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Seri</label>
                        <input type="text" name="serial_number" placeholder="Misal: YM-2023-991"
                               class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Kapasitas / Spesifikasi</label>
                        <input type="text" name="capacity_specs" placeholder="Misal: 9 Kg / 15 Bar"
                               class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Instalasi</label>
                        <input type="date" name="installation_date"
                               class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Tambahan</label>
                    <textarea name="notes" rows="2" placeholder="Catatan posisi penempatan atau instruksi..."
                              class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none"></textarea>
                </div>

                <div class="flex justify-end space-x-2 pt-3 border-t border-slate-200">
                    <button type="button" @click="addModalOpen = false" class="px-3 py-2 text-xs font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-[#D71920] hover:bg-[#b8141a] rounded-lg transition">
                        Simpan Sarfas
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
