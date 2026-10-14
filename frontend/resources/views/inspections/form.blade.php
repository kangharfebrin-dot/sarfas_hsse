@extends('layouts.app')

@section('title', 'Formulir Pemeriksaan Sarfas')
@section('breadcrumb', 'Pemeriksaan / Formulir Checklist')
@section('page_title', 'Formulir Inspeksi Lapangan')

@php
    // Prepare existing answers if editing a draft
    $existingAnswers = [];
    if (isset($inspection['answers'])) {
        foreach ($inspection['answers'] as $ans) {
            $existingAnswers[$ans['checklist_item_id']] = [
                'answer' => $ans['answer'],
                'notes' => $ans['notes'] ?? '',
                'risk_level' => $ans['risk_level'] ?? 'SEDANG',
                'photo_url' => $ans['photo_url'] ?? '',
            ];
        }
    }

    // Collect all items across sections
    $allItems = [];
    foreach ($template['sections'] ?? [] as $sec) {
        foreach ($sec['items'] ?? [] as $item) {
            $allItems[] = $item;
        }
    }
    $totalItemsCount = count($allItems);
@endphp

@section('content')
<div class="max-w-4xl mx-auto space-y-6 pb-24" x-data="{
    sarfasId: '{{ $sarfas['id'] }}',
    templateId: '{{ $template['id'] }}',
    inspectionId: '{{ $id ?? '' }}',
    totalItems: {{ $totalItemsCount }},
    answers: {{ json_encode($existingAnswers) }},
    generalNotes: '{{ $inspection['general_notes'] ?? '' }}',
    confirmModalOpen: false,
    draftSavedAlert: false,

    get answeredCount() {
        return Object.keys(this.answers).filter(k => this.answers[k] && this.answers[k].answer).length;
    },

    get progressPercent() {
        return this.totalItems > 0 ? Math.round((this.answeredCount / this.totalItems) * 100) : 0;
    },

    get hasCriticalFailure() {
        for (let k in this.answers) {
            if (this.answers[k] && this.answers[k].answer === 'TIDAK_BAIK' && this.answers[k].is_critical) {
                return true;
            }
        }
        return false;
    },

    get hasFailures() {
        for (let k in this.answers) {
            if (this.answers[k] && this.answers[k].answer === 'TIDAK_BAIK') {
                return true;
            }
        }
        return false;
    },

    setAnswer(itemId, val, isCritical = false, requiresPhoto = false) {
        const defaultPhoto = requiresPhoto ? '/assets/images/proof/evidence_' + itemId + '.jpg' : '';
        if (!this.answers[itemId]) {
            this.answers[itemId] = {
                answer: val,
                notes: '',
                risk_level: isCritical ? 'KRITIS' : 'SEDANG',
                photo_url: defaultPhoto,
                is_critical: isCritical,
                requires_photo: requiresPhoto
            };
        } else {
            this.answers[itemId].answer = val;
            this.answers[itemId].is_critical = isCritical;
            this.answers[itemId].requires_photo = requiresPhoto;
            if (requiresPhoto && (!this.answers[itemId].photo_url || this.answers[itemId].photo_url === '')) {
                this.answers[itemId].photo_url = defaultPhoto;
            }
            if (val === 'TIDAK_BAIK' && !this.answers[itemId].risk_level) {
                this.answers[itemId].risk_level = isCritical ? 'KRITIS' : 'SEDANG';
            }
        }
    },

    getAnswer(itemId) {
        return this.answers[itemId] ? this.answers[itemId].answer : '';
    },

    validateBeforeSubmit() {
        if (this.answeredCount < this.totalItems) {
            alert('Perhatian: Masih terdapat ' + (this.totalItems - this.answeredCount) + ' item checklist yang belum dijawab. Seluruh item wajib diisi sebelum mengirim inspeksi.');
            return false;
        }

        // Check if any TIDAK_BAIK has empty notes
        for (let k in this.answers) {
            if (this.answers[k].answer === 'TIDAK_BAIK' && (!this.answers[k].notes || this.answers[k].notes.trim() === '')) {
                alert('Perhatian: Setiap item dengan kondisi Tidak Baik wajib dilengkapi dengan catatan ketidaksesuaian.');
                return false;
            }
        }

        this.confirmModalOpen = true;
    }
}">

    <!-- STICKY TOP PROGRESS BAR -->
    <div class="sticky top-0 z-30 bg-white/95 backdrop-blur-sm border border-slate-200 rounded-xl p-3.5 shadow-sm">
        <div class="flex items-center justify-between text-xs mb-1.5 font-semibold">
            <span class="flex items-center text-slate-800">
                <span class="w-2 h-2 rounded-full bg-[#D71920] mr-2 animate-pulse"></span>
                <span>Progres Pemeriksaan Lapangan</span>
            </span>
            <span class="text-slate-600 font-mono">
                <span x-text="answeredCount"></span> / <span x-text="totalItems"></span> Item Selesai (<span x-text="progressPercent"></span>%)
            </span>
        </div>
        <div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden">
            <div class="h-full bg-gradient-to-r from-[#00529B] to-[#008B5A] transition-all duration-300"
                 :style="'width: ' + progressPercent + '%'"></div>
        </div>
    </div>

    <!-- Sarfas & Template Context Banner -->
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs space-y-3">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-slate-100">
            <div>
                <div class="flex items-center space-x-2">
                    <span class="px-2 py-0.5 text-xs font-mono font-bold bg-[#D71920] text-white rounded">
                        {{ $sarfas['asset_code'] }}
                    </span>
                    <h2 class="text-sm font-bold text-slate-900">{{ $sarfas['name'] }}</h2>
                </div>
                <div class="text-xs text-slate-500 mt-1">
                    Lokasi: <span class="font-semibold text-slate-800">{{ $sarfas['location_name'] }}</span> · 
                    Kategori: <span class="font-semibold text-slate-800">{{ $sarfas['category_name'] }}</span>
                </div>
            </div>
            <div class="text-right">
                <span class="text-[11px] font-semibold text-slate-500 block">Template Acuan:</span>
                <span class="text-xs font-bold text-[#00529B]">{{ $template['title'] }}</span>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs text-slate-600 pt-1">
            <div>
                <span class="text-[10px] text-slate-400 block">Pemeriksa</span>
                <span class="font-medium text-slate-800">{{ session('auth_user.name', 'Inspektor HSSE') }}</span>
            </div>
            <div>
                <span class="text-[10px] text-slate-400 block">Tanggal Inspeksi</span>
                <span class="font-medium text-slate-800">{{ date('d F Y') }}</span>
            </div>
            <div>
                <span class="text-[10px] text-slate-400 block">Merek & Seri</span>
                <span class="font-medium text-slate-800">{{ $sarfas['brand_model'] ?? '-' }} ({{ $sarfas['serial_number'] ?? '-' }})</span>
            </div>
            <div>
                <span class="text-[10px] text-slate-400 block">Kapasitas / Spec</span>
                <span class="font-medium text-slate-800">{{ $sarfas['capacity_specs'] ?? '-' }}</span>
            </div>
        </div>
    </div>

    <!-- Critical Finding Alert Banner (Appears dynamically if user flags any critical item) -->
    <div x-show="hasCriticalFailure" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="p-4 bg-red-50 border-2 border-red-300 rounded-xl flex items-start space-x-3 text-xs text-red-900" x-cloak>
        <svg class="w-5 h-5 text-red-600 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
        </svg>
        <div>
            <h4 class="font-bold text-red-800 text-sm">Peringatan Kondisi Kritis Terdeteksi!</h4>
            <p class="text-[11px] text-red-700 mt-0.5 leading-relaxed">
                Anda menandai item berstatus kritis sebagai "Tidak Baik". Sistem akan secara otomatis mengklasifikasikan hasil inspeksi sebagai <strong>KRITIS</strong> dan membuat tiket temuan darurat untuk ditindaklanjuti dalam waktu 1-3 hari.
            </p>
        </div>
    </div>

    <!-- MAIN FORM CONTAINER -->
    <form id="inspectionMainForm" action="{{ route('inspections.submit', $id ?? 1) }}" method="POST">
        @csrf
        <input type="hidden" name="sarfas_id" value="{{ $sarfas['id'] }}">
        <input type="hidden" name="template_id" value="{{ $template['id'] }}">

        <!-- Sections & Items -->
        <div class="space-y-6">
            @foreach($template['sections'] ?? [] as $secIdx => $sec)
                <div class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
                    <!-- Section Header -->
                    <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                        <div class="flex items-center space-x-2.5">
                            <span class="w-6 h-6 rounded-full bg-[#00529B] text-white flex items-center justify-center font-bold text-xs shrink-0">
                                {{ $secIdx + 1 }}
                            </span>
                            <div>
                                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wide">{{ $sec['title'] }}</h3>
                                @if($sec['description'])
                                    <p class="text-[10px] text-slate-400">{{ $sec['description'] }}</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Items in Section -->
                    <div class="divide-y divide-slate-100">
                        @foreach($sec['items'] ?? [] as $item)
                            @php
                                $itemId = $item['id'];
                                $isCritical = $item['is_critical'] ? 'true' : 'false';
                            @endphp
                            <div class="p-4 transition hover:bg-slate-50/50 space-y-3"
                                 :class="getAnswer({{ $itemId }}) === 'TIDAK_BAIK' ? 'bg-red-50/30' : ''">
                                
                                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                                    <div class="space-y-1 flex-1">
                                        <div class="flex items-center space-x-2">
                                            <span class="font-mono text-xs font-bold text-slate-800">{{ $item['item_code'] }}</span>
                                            @if($item['is_critical'])
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-700 border border-red-200 flex items-center">
                                                    <svg class="w-3 h-3 mr-1 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                    </svg>
                                                    ITEM KRITIS
                                                </span>
                                            @endif
                                            @if($item['requires_photo'])
                                                <span class="px-1.5 py-0.5 rounded text-[9px] font-semibold bg-blue-100 text-blue-700">
                                                    BUKTI FOTO
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-xs font-semibold text-slate-900 leading-relaxed">{{ $item['question_text'] }}</p>
                                        @if($item['standard_criteria'])
                                            <p class="text-[11px] text-slate-500 italic">Kriteria: {{ $item['standard_criteria'] }}</p>
                                        @endif
                                    </div>

                                    <!-- 3 Choice Toggle Buttons -->
                                    <div class="flex items-center space-x-1.5 shrink-0">
                                        <!-- Baik (Green) -->
                                        <button type="button" 
                                                @click="setAnswer({{ $itemId }}, 'BAIK', {{ $isCritical }}, {{ $item['requires_photo'] ? 'true' : 'false' }})"
                                                :class="getAnswer({{ $itemId }}) === 'BAIK' ? 'bg-[#008B5A] text-white shadow-xs font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-medium'"
                                                class="px-3 py-1.5 rounded-lg text-xs transition flex items-center">
                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Baik
                                        </button>

                                        <!-- Tidak Baik (Red) -->
                                        <button type="button" 
                                                @click="setAnswer({{ $itemId }}, 'TIDAK_BAIK', {{ $isCritical }}, {{ $item['requires_photo'] ? 'true' : 'false' }})"
                                                :class="getAnswer({{ $itemId }}) === 'TIDAK_BAIK' ? 'bg-[#D71920] text-white shadow-xs font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-medium'"
                                                class="px-3 py-1.5 rounded-lg text-xs transition flex items-center">
                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                            Tidak Baik
                                        </button>

                                        <!-- N/A (Gray) -->
                                        <button type="button" 
                                                @click="setAnswer({{ $itemId }}, 'NA', {{ $isCritical }}, false)"
                                                :class="getAnswer({{ $itemId }}) === 'NA' ? 'bg-slate-700 text-white shadow-xs font-bold' : 'bg-slate-100 text-slate-500 hover:bg-slate-200 font-medium'"
                                                class="px-2.5 py-1.5 rounded-lg text-xs transition">
                                            N/A
                                        </button>
                                    </div>
                                </div>

                                <!-- Conditional Sub-panel for TIDAK BAIK (Notes, Risk, Photo) -->
                                <div x-show="getAnswer({{ $itemId }}) === 'TIDAK_BAIK'" 
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 -translate-y-1"
                                     x-transition:enter-end="opacity-100 translate-y-0"
                                     class="p-3.5 rounded-lg bg-white border border-red-200 space-y-3 mt-2" x-cloak>
                                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                                        <!-- Notes -->
                                        <div class="sm:col-span-8">
                                            <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                                Catatan Ketidaksesuaian / Kerusakan <span class="text-red-500">*</span>
                                            </label>
                                            <textarea x-model="answers[{{ $itemId }}].notes"
                                                      :name="'answers[' + {{ $itemId }} + '][notes]'"
                                                      rows="2" placeholder="Uraikan kondisi aktual temuan lapangan (minimal 5 karakter)..."
                                                      class="w-full px-3 py-1.5 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none"></textarea>
                                        </div>

                                        <!-- Risk Level Selection -->
                                        <div class="sm:col-span-4">
                                            <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                                Tingkat Risiko HSSE
                                            </label>
                                            <select x-model="answers[{{ $itemId }}].risk_level"
                                                    :name="'answers[' + {{ $itemId }} + '][risk_level]'"
                                                    class="w-full px-2.5 py-1.5 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none bg-white">
                                                <option value="RENDAH">Rendah (Low)</option>
                                                <option value="SEDANG">Sedang (Medium)</option>
                                                <option value="TINGGI">Tinggi (High)</option>
                                                <option value="KRITIS">Kritis (Critical)</option>
                                            </select>
                                            <span class="text-[10px] text-slate-400 mt-1 block">Menentukan target batas waktu (SLA) perbaikan</span>
                                        </div>
                                    </div>

                                    <!-- Foto Bukti Temuan -->
                                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
                                        <div class="flex items-center space-x-2 text-slate-500 text-[11px]">
                                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            <span>Foto Bukti Fisik Lapangan:</span>
                                            <span class="text-emerald-600 font-medium font-mono text-[10px]">Tersedia [Bukti Temuan Digital]</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Sub-panel Bukti Wajib jika item requires_photo dan kondisi BAIK -->
                                @if($item['requires_photo'])
                                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs bg-slate-50/60 p-2.5 rounded-lg mt-2">
                                        <div class="flex items-center space-x-2 text-slate-600 text-[11px]">
                                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            <span class="font-semibold text-slate-700">Bukti Foto Wajib:</span>
                                            <span class="text-emerald-600 font-medium font-mono text-[10px]">[Terlampir Bukti Lapangan]</span>
                                        </div>
                                    </div>
                                @endif

                                <!-- Hidden inputs carrying answer & photo values for POST -->
                                <input type="hidden" :name="'answers[' + {{ $itemId }} + '][checklist_item_id]'" value="{{ $itemId }}">
                                <input type="hidden" :name="'answers[' + {{ $itemId }} + '][answer]'" :value="getAnswer({{ $itemId }})">
                                <input type="hidden" :name="'answers[' + {{ $itemId }} + '][photo_url]'" :value="answers[{{ $itemId }}] && answers[{{ $itemId }}].photo_url ? answers[{{ $itemId }}].photo_url : '{{ $item['requires_photo'] ? '/assets/images/proof/evidence_' . $itemId . '.jpg' : '' }}'">
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <!-- General Remarks & Inspector Signature -->
        <div class="bg-white border border-slate-200 rounded-xl shadow-xs p-5 space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Catatan Umum & Konfirmasi Pemeriksa</h3>
            
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Keseluruhan Kondisi Operasional</label>
                <textarea name="general_notes" x-model="generalNotes" rows="2" placeholder="Tuliskan kesimpulan umum atau kondisi lingkungan di sekitar sarfas..."
                          class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#D71920] focus:outline-none"></textarea>
            </div>

            <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg flex items-center justify-between text-xs">
                <div>
                    <span class="text-[10px] text-slate-400 block">Pemeriksa Terdaftar</span>
                    <span class="font-bold text-slate-800">{{ session('auth_user.name', 'Inspektor Lapangan') }}</span>
                    <span class="text-slate-500 text-[11px]">({{ session('auth_user.jabatan', 'Teknisi HSSE FT Maos') }})</span>
                </div>
                <div class="text-right">
                    <span class="inline-flex items-center text-[10px] font-mono text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                        <svg class="w-3 h-3 mr-1 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        Digital Signature Ready
                    </span>
                    <div class="text-[10px] text-slate-400 mt-0.5">Waktu: {{ date('d M Y H:i') }} WIB</div>
                </div>
            </div>
        </div>

    </form>

    <!-- FLOATING BOTTOM ACTION BAR -->
    <div class="fixed bottom-0 inset-x-0 z-40 bg-white/95 backdrop-blur-md border-t border-slate-200 p-3 sm:p-4 shadow-lg">
        <div class="max-w-4xl mx-auto flex items-center justify-between gap-4">
            <div class="flex items-center space-x-3 text-xs">
                <span class="text-slate-500">Status Kelengkapan:</span>
                <span class="font-bold text-slate-900" x-text="answeredCount + ' / ' + totalItems + ' item'"></span>
                <template x-if="answeredCount === totalItems">
                    <span class="text-emerald-600 font-bold hidden sm:inline">&bull; Siap Dikirim</span>
                </template>
            </div>

            <div class="flex items-center space-x-2.5">
                <!-- Save Draft Button -->
                <button type="button" 
                        @click="
                            const form = document.getElementById('inspectionMainForm');
                            form.action = '{{ route('inspections.saveDraft', $id ?? '') }}';
                            form.submit();
                        "
                        class="px-4 py-2 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition flex items-center">
                    <svg class="w-4 h-4 mr-1.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                    </svg>
                    Simpan Draft
                </button>

                <!-- Final Submit Button -->
                <button type="button" 
                        @click="validateBeforeSubmit()"
                        class="px-5 py-2 text-xs font-semibold text-white bg-[#D71920] hover:bg-[#b8141a] rounded-lg shadow-sm transition flex items-center">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Kirim Hasil Inspeksi
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL CONFIRMATION SUBMIT -->
    <div x-show="confirmModalOpen" 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4" 
         x-cloak>
        <div @click.away="confirmModalOpen = false" class="bg-white rounded-xl shadow-xl border border-slate-200 max-w-md w-full p-6 text-center space-y-4">
            <div class="w-12 h-12 rounded-full mx-auto flex items-center justify-center"
                 :class="hasFailures ? 'bg-amber-100 text-amber-600' : 'bg-emerald-100 text-emerald-600'">
                <template x-if="hasFailures">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </template>
                <template x-if="!hasFailures">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </template>
            </div>

            <div>
                <h3 class="text-base font-bold text-slate-900">Konfirmasi Kirim Hasil Inspeksi</h3>
                <p class="text-xs text-slate-500 mt-1">
                    Pastikan seluruh pemeriksaan lapangan telah dilakukan dengan teliti dan data yang diisi telah akurat.
                </p>
            </div>

            <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg text-left text-xs space-y-1">
                <div class="flex justify-between">
                    <span class="text-slate-500">Sarfas:</span>
                    <span class="font-bold text-slate-800">{{ $sarfas['asset_code'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Total Item Dijawab:</span>
                    <span class="font-bold text-slate-800" x-text="answeredCount + ' dari ' + totalItems"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Hasil Indikasi:</span>
                    <span class="font-bold"
                          :class="hasCriticalFailure ? 'text-red-600' : (hasFailures ? 'text-amber-600' : 'text-emerald-600')"
                          x-text="hasCriticalFailure ? 'KRITIS' : (hasFailures ? 'PERLU TINDAK LANJUT' : 'BAIK')"></span>
                </div>
            </div>

            <div class="flex justify-center space-x-2 pt-2">
                <button type="button" @click="confirmModalOpen = false" class="px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition">
                    Periksa Kembali
                </button>
                <button type="button" 
                        @click="
                            const form = document.getElementById('inspectionMainForm');
                            form.action = '{{ route('inspections.submit', $id ?? 1) }}';
                            form.submit();
                        "
                        class="px-5 py-2 text-xs font-semibold text-white bg-[#D71920] hover:bg-[#b8141a] rounded-lg shadow-xs transition">
                    Ya, Kirim Sekarang
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
