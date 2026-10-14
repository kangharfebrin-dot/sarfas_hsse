@extends('layouts.app')

@section('title', 'Mulai Inspeksi Baru')
@section('breadcrumb', 'Pemeriksaan / Mulai Baru')
@section('page_title', 'Pilih Sarfas & Template Checklist')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{
    selectedSarfasId: '{{ $selectedSarfasId ?? '' }}',
    selectedTemplateId: '',
    sarfasList: {{ json_encode($sarfasList) }},
    templates: {{ json_encode($templates) }},
    get activeSarfas() {
        return this.sarfasList.find(s => s.id == this.selectedSarfasId) || null;
    },
    get filteredTemplates() {
        if (!this.activeSarfas) return this.templates;
        const matching = this.templates.filter(t => t.category_id == this.activeSarfas.category_id);
        return matching.length > 0 ? matching : this.templates;
    }
}">

    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-xs space-y-6">
        <div class="border-b border-slate-200 pb-4">
            <span class="text-[11px] font-bold text-[#D71920] uppercase tracking-wider">Langkah 1 dari 2</span>
            <h2 class="text-base font-bold text-slate-900 mt-1">Inisiasi Pemeriksaan Lapangan</h2>
            <p class="text-xs text-slate-500 mt-0.5">Tentukan sarana & fasilitas yang akan diperiksa beserta template checklist standar yang relevan.</p>
        </div>

        <form action="{{ route('inspections.form') }}" method="GET" class="space-y-6">
            <!-- 1. Select Sarfas -->
            <div>
                <label class="block text-xs font-bold text-slate-800 mb-1.5">
                    1. Pilih Peralatan / Sarfas yang Akan Diperiksa <span class="text-red-500">*</span>
                </label>
                <select name="sarfas_id" x-model="selectedSarfasId" required
                        class="w-full px-3.5 py-2.5 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none bg-white font-medium">
                    <option value="">-- Pilih Sarfas dari Inventaris --</option>
                    @foreach($sarfasList as $s)
                        <option value="{{ $s['id'] }}">
                            [{{ $s['asset_code'] }}] {{ $s['name'] }} — Lokasi: {{ $s['location_name'] }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Active Sarfas Information Preview Card -->
            <template x-if="activeSarfas">
                <div class="p-4 rounded-xl border border-blue-200 bg-blue-50/40 space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span class="px-2 py-0.5 text-xs font-mono font-bold bg-[#00529B] text-white rounded" x-text="activeSarfas.asset_code"></span>
                            <h4 class="text-xs font-bold text-slate-900" x-text="activeSarfas.name"></h4>
                        </div>
                        <span class="text-[11px] font-bold px-2 py-0.5 rounded bg-white border border-slate-200" x-text="activeSarfas.condition_status"></span>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs pt-2 border-t border-blue-100 text-slate-600">
                        <div>
                            <span class="text-[10px] text-slate-400 block">Kategori</span>
                            <span class="font-semibold text-slate-800" x-text="activeSarfas.category_name"></span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 block">Lokasi</span>
                            <span class="font-semibold text-slate-800" x-text="activeSarfas.location_name"></span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 block">Merek / Seri</span>
                            <span class="font-semibold text-slate-800" x-text="(activeSarfas.brand_model || '-') + ' / ' + (activeSarfas.serial_number || '-')"></span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 block">Kapasitas</span>
                            <span class="font-semibold text-slate-800" x-text="activeSarfas.capacity_specs || '-'"></span>
                        </div>
                    </div>
                </div>
            </template>

            <!-- 2. Select Checklist Template -->
            <div>
                <label class="block text-xs font-bold text-slate-800 mb-1.5">
                    2. Pilih Template Checklist Standar HSSE <span class="text-red-500">*</span>
                </label>
                <select name="template_id" x-model="selectedTemplateId" required
                        class="w-full px-3.5 py-2.5 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none bg-white font-medium">
                    <option value="">-- Pilih Template Checklist --</option>
                    <template x-for="tmpl in filteredTemplates" :key="tmpl.id">
                        <option :value="tmpl.id" x-text="tmpl.title + ' (' + tmpl.template_code + ' - ' + tmpl.inspection_frequency + ')'"></option>
                    </template>
                </select>
                <p class="text-[11px] text-slate-400 mt-1">Sistem otomatis menyaring checklist yang cocok dengan kategori sarfas yang dipilih.</p>
            </div>

            <!-- Submit Step 1 Button -->
            <div class="pt-4 border-t border-slate-200 flex items-center justify-between">
                <a href="{{ route('inspections.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800">
                    &larr; Batalkan
                </a>
                <button type="submit" 
                        :disabled="!selectedSarfasId || !selectedTemplateId"
                        :class="(!selectedSarfasId || !selectedTemplateId) ? 'opacity-50 cursor-not-allowed bg-slate-300 text-slate-500' : 'bg-[#D71920] hover:bg-[#b8141a] text-white shadow-xs'"
                        class="px-5 py-2.5 text-xs font-semibold rounded-lg transition flex items-center">
                    <span>Mulai Mengisi Formulir Checklist</span>
                    <svg class="w-4 h-4 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
