@extends('layouts.app')

@section('title', 'Template: ' . $template['template_code'])
@section('breadcrumb', 'Master Data / Checklist / ' . $template['template_code'])
@section('page_title', $template['title'])

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar -->
    <div class="flex items-center justify-between">
        <a href="{{ route('master-checklist.index') }}" class="inline-flex items-center text-xs font-semibold text-slate-600 hover:text-slate-900">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Master Checklist
        </a>
        <div class="flex items-center space-x-2">
            <span class="text-xs font-mono font-bold text-[#00529B] bg-blue-50 px-2.5 py-1 rounded border border-blue-200">
                {{ $template['template_code'] }} (v{{ $template['version'] }})
            </span>
        </div>
    </div>

    <!-- Template Header Info -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-xs p-6 space-y-4">
        <div class="flex items-start justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-900">{{ $template['title'] }}</h2>
                <p class="text-xs text-slate-500 mt-1">{{ $template['description'] }}</p>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                {{ $template['inspection_frequency'] }}
            </span>
        </div>

        <div class="flex items-center space-x-6 text-xs text-slate-600 pt-3 border-t border-slate-100">
            <div>Kategori: <strong class="text-slate-800">{{ $template['category_name'] }}</strong></div>
            <div>Status: <span class="text-emerald-600 font-bold">Aktif & Digunakan</span></div>
            <div>Pilihan Jawaban Standar: <span class="font-medium text-slate-700">Baik / Tidak Baik / N/A</span></div>
        </div>
    </div>

    <!-- Sections and Items List -->
    <div class="space-y-4">
        @forelse($template['sections'] ?? [] as $secIdx => $sec)
            <div class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
                <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                    <div class="flex items-center space-x-2.5">
                        <span class="w-6 h-6 rounded-full bg-[#00529B] text-white flex items-center justify-center font-bold text-xs">
                            {{ $secIdx + 1 }}
                        </span>
                        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wide">{{ $sec['title'] }}</h3>
                    </div>
                    <span class="text-[11px] text-slate-500">{{ count($sec['items'] ?? []) }} Item Pemeriksaan</span>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse($sec['items'] ?? [] as $item)
                        <div class="p-4 hover:bg-slate-50 transition flex items-start justify-between gap-4">
                            <div class="space-y-1">
                                <div class="flex items-center space-x-2">
                                    <span class="font-mono text-xs font-bold text-slate-800">{{ $item['item_code'] }}</span>
                                    @if($item['is_critical'])
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-700 border border-red-200">
                                            KRITIS (NFPA/HSSE)
                                        </span>
                                    @endif
                                    @if($item['requires_photo'])
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-100 text-blue-700">
                                            WAJIB FOTO
                                        </span>
                                    @endif
                                </div>
                                <p class="text-xs font-medium text-slate-900 leading-relaxed">{{ $item['question_text'] }}</p>
                                @if($item['standard_criteria'])
                                    <p class="text-[11px] text-slate-500 italic">Standar: {{ $item['standard_criteria'] }}</p>
                                @endif
                            </div>
                            <div class="shrink-0 text-right">
                                <span class="px-2 py-1 text-[11px] rounded bg-slate-100 text-slate-600 font-medium">
                                    Urutan #{{ $item['sort_order'] }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="p-4 text-xs text-center text-slate-400">Belum ada item dalam bagian ini.</div>
                    @endforelse
                </div>
            </div>
        @empty
            <div class="bg-white p-8 rounded-xl border border-slate-200 text-center text-slate-400 text-xs">
                Tidak ada kelompok bagian pemeriksaan dalam template ini.
            </div>
        @endforelse
    </div>

</div>
@endsection
