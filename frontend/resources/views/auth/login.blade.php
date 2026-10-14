<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Checklist Sarfas HSSE FT Maos</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased text-slate-800 bg-[#F5F7FA] flex items-center justify-center p-4">

    <div class="w-full max-w-4xl bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden grid grid-cols-1 lg:grid-cols-12">
        
        <!-- Left Hero / Brand Identity Section -->
        <div class="lg:col-span-5 bg-gradient-to-br from-[#D71920] to-[#990F14] text-white p-8 flex flex-col justify-between relative overflow-hidden">
            <!-- Decorative Pattern Accents -->
            <div class="absolute -top-10 -left-10 w-40 h-40 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
            <div class="absolute -bottom-10 -right-10 w-48 h-48 bg-[#00529B]/30 rounded-full blur-2xl pointer-events-none"></div>

            <div>
                <!-- Brand Badge -->
                <div class="inline-flex items-center space-x-2 px-3 py-1.5 rounded-full bg-white/15 backdrop-blur-sm text-xs font-semibold tracking-wider text-white mb-6">
                    <span class="w-2 h-2 rounded-full bg-[#008B5A]"></span>
                    <span>PT PERTAMINA PATRA NIAGA</span>
                </div>

                <h1 class="text-2xl font-bold tracking-tight text-white leading-snug">
                    Sistem Digitalisasi<br>Checklist Sarfas HSSE
                </h1>
                <p class="text-xs text-red-100 mt-2 font-medium">
                    Fuel Terminal Maos · Regional Jawa Bagian Tengah
                </p>

                <p class="text-xs text-red-100/80 mt-6 leading-relaxed">
                    Platform terpadu inspeksi rutin sarana dan fasilitas HSSE, pemantauan kelayakan proteksi kebakaran, peralatan darurat, serta penanganan temuan lapangan.
                </p>
            </div>

            <div class="mt-8 pt-6 border-t border-white/20">
                <div class="text-[11px] text-red-100 flex items-center space-x-2">
                    <svg class="w-4 h-4 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    <span>Terhubung ke REST API Backend & MySQL</span>
                </div>
            </div>
        </div>

        <!-- Right Form & Quick Demo Switcher -->
        <div class="lg:col-span-7 p-8 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-6">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Masuk Akun</h2>
                        <p class="text-xs text-slate-500">Gunakan akun demonstrasi untuk menguji seluruh modul</p>
                    </div>
                    <span class="px-2 py-1 text-[10px] font-bold tracking-wider text-amber-700 bg-amber-50 border border-amber-200 rounded">
                        MOCKUP & DEMO
                    </span>
                </div>

                @if(session('error'))
                    <div class="p-3 mb-4 text-xs text-red-700 bg-red-50 border border-red-200 rounded-lg flex items-center">
                        <svg class="w-4 h-4 mr-2 shrink-0 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="p-3 mb-4 text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-lg">
                        {{ session('warning') }}
                    </div>
                @endif

                <!-- Standard Form -->
                <form action="{{ route('login.process') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="email" class="block text-xs font-semibold text-slate-700 mb-1">Email Pertamina</label>
                        <input type="email" id="email" name="email" value="{{ old('email', 'superadmin@pertamina.demo') }}" required
                               class="w-full px-3.5 py-2 text-xs border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D71920] focus:border-transparent transition">
                    </div>

                    <div>
                        <label for="password" class="block text-xs font-semibold text-slate-700 mb-1">Kata Sandi</label>
                        <input type="password" id="password" name="password" value="password123" required
                               class="w-full px-3.5 py-2 text-xs border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D71920] focus:border-transparent transition">
                    </div>

                    <button type="submit" 
                            class="w-full py-2.5 px-4 bg-[#D71920] hover:bg-[#b8141a] text-white text-xs font-semibold rounded-lg shadow-sm transition flex items-center justify-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Masuk ke Dashboard
                    </button>
                </form>

                <!-- Quick Demo Role Switcher -->
                <div class="mt-6 pt-5 border-t border-slate-100">
                    <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-2.5 flex items-center">
                        <svg class="w-3.5 h-3.5 mr-1 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        Pilih Cepat Akun Demo (Satu Klik):
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach($demoAccounts as $demo)
                            <form action="{{ route('login.quick') }}" method="POST">
                                @csrf
                                <input type="hidden" name="email" value="{{ $demo['email'] }}">
                                <input type="hidden" name="password" value="{{ $demo['default_password'] ?? 'password123' }}">
                                <button type="submit" class="w-full text-left p-2.5 rounded-lg border border-slate-200 hover:border-[#D71920] hover:bg-red-50/30 transition group">
                                    <div class="text-xs font-bold text-slate-800 group-hover:text-[#D71920] flex items-center justify-between">
                                        <span class="truncate">{{ $demo['role_name'] }}</span>
                                        <span class="text-[9px] px-1.5 py-0.2 bg-slate-100 group-hover:bg-red-100 text-slate-600 rounded">Login</span>
                                    </div>
                                    <div class="text-[10px] text-slate-500 truncate mt-0.5">{{ $demo['name'] }}</div>
                                </button>
                            </form>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="mt-6 pt-3 text-center border-t border-slate-100 text-[10px] text-slate-400">
                Checklist Sarfas HSSE · Fuel Terminal Maos &copy; 2026 PT Pertamina Patra Niaga
            </div>
        </div>

    </div>

</body>
</html>
