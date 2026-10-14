<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Checklist Sarfas HSSE FT Maos</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Compiled Vite Assets (Tailwind CSS, Alpine.js, Chart.js) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
    </style>
    @stack('styles')
</head>
<body class="h-full font-sans antialiased text-slate-800 bg-[#F5F7FA]" x-data="{ sidebarOpen: false, sidebarCollapsed: false, profileOpen: false, notificationOpen: false }">

    <!-- Mobile Sidebar Backdrop -->
    <div x-show="sidebarOpen" 
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-40 bg-slate-900/60 lg:hidden" 
         @click="sidebarOpen = false" x-cloak></div>

    <div class="flex h-full">
        <!-- SIDEBAR -->
        <aside :class="{ 'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen, 'w-64': !sidebarCollapsed, 'w-20': sidebarCollapsed }"
               class="fixed inset-y-0 left-0 z-50 flex flex-col transition-all duration-300 ease-in-out bg-white border-r border-slate-200 lg:static lg:translate-x-0">
            
            <!-- Logo & Brand Header -->
            <div class="flex items-center justify-between h-16 px-4 border-b border-slate-200 bg-white">
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 overflow-hidden">
                    <div class="flex items-center justify-center w-10 h-10 font-bold text-white rounded-lg shadow-sm shrink-0 bg-[#D71920]">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <div x-show="!sidebarCollapsed" class="flex flex-col overflow-hidden text-left transition-opacity duration-200" x-cloak>
                        <span class="text-sm font-bold tracking-tight text-slate-900 leading-none">PERTAMINA</span>
                        <span class="text-[11px] font-semibold text-[#00529B] leading-tight">HSSE · FT MAOS</span>
                        <span class="text-[9px] text-slate-400 truncate">Checklist Sarfas v1.0</span>
                    </div>
                </a>
                <button @click="sidebarCollapsed = !sidebarCollapsed" class="hidden p-1 text-slate-400 rounded hover:text-slate-600 hover:bg-slate-100 lg:block" title="Ciutkan Sidebar">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                    </svg>
                </button>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                @php
                    $role = session('auth_user.role_slug', 'super_admin');
                    $navItems = [
                        ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                        ['route' => 'sarfas.index', 'label' => 'Data Sarfas', 'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'],
                        ['route' => 'inspections.index', 'label' => 'Checklist Inspeksi', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'],
                        ['route' => 'findings.index', 'label' => 'Temuan & Tindak Lanjut', 'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z', 'badge' => '2'],
                        ['route' => 'schedules.index', 'label' => 'Jadwal Inspeksi', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                        ['route' => 'reports.index', 'label' => 'Laporan Inspeksi', 'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                        ['route' => 'master-checklist.index', 'label' => 'Master Checklist', 'icon' => 'M4 6h16M4 10h16M4 14h16M4 18h16', 'roles' => ['super_admin', 'admin_hsse']],
                        ['route' => 'users.index', 'label' => 'Manajemen Pengguna', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', 'roles' => ['super_admin', 'admin_hsse']],
                        ['route' => 'audit-logs.index', 'label' => 'Riwayat Aktivitas', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'roles' => ['super_admin', 'admin_hsse']],
                    ];
                @endphp

                @foreach($navItems as $item)
                    @if(!isset($item['roles']) || in_array($role, $item['roles']))
                        @php
                            $isActive = request()->routeIs($item['route'].'*');
                        @endphp
                        <a href="{{ route($item['route']) }}"
                           class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors group {{ $isActive ? 'bg-[#D71920]/10 text-[#D71920] font-semibold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}"
                           :title="sidebarCollapsed ? '{{ $item['label'] }}' : ''">
                            <svg class="w-5 h-5 shrink-0 transition-colors {{ $isActive ? 'text-[#D71920]' : 'text-slate-400 group-hover:text-slate-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}" />
                            </svg>
                            <span x-show="!sidebarCollapsed" class="ml-3 truncate" x-cloak>{{ $item['label'] }}</span>
                            @if(isset($item['badge']) && $item['badge'] > 0)
                                <span x-show="!sidebarCollapsed" class="ml-auto inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-[#DC2626] text-white" x-cloak>
                                    {{ $item['badge'] }}
                                </span>
                            @endif
                        </a>
                    @endif
                @endforeach
            </nav>

            <!-- Operational Unit Footer -->
            <div class="p-3 border-t border-slate-200 bg-slate-50/50">
                <div class="flex items-center px-2 py-2 text-xs rounded-lg text-slate-500 bg-slate-100/80">
                    <span class="w-2 h-2 mr-2 rounded-full bg-emerald-500 shrink-0"></span>
                    <span x-show="!sidebarCollapsed" class="truncate font-medium text-slate-600" x-cloak>Fuel Terminal Maos</span>
                </div>
            </div>
        </aside>

        <!-- MAIN CONTENT AREA -->
        <div class="flex flex-col flex-1 min-w-0 overflow-hidden">
            <!-- TOPBAR -->
            <header class="flex items-center justify-between h-16 px-4 bg-white border-b border-slate-200 sm:px-6">
                <div class="flex items-center space-x-3">
                    <!-- Mobile Hamburger -->
                    <button @click="sidebarOpen = true" class="p-2 text-slate-500 rounded-lg lg:hidden hover:bg-slate-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <!-- Page Breadcrumb & Title -->
                    <div>
                        <div class="text-xs font-medium text-slate-400">@yield('breadcrumb', 'PT Pertamina Patra Niaga')</div>
                        <h1 class="text-lg font-bold text-slate-800 leading-tight">@yield('page_title', 'Dashboard')</h1>
                    </div>
                </div>

                <!-- Right Topbar Controls -->
                <div class="flex items-center space-x-3">
                    <!-- Demo Role Switcher Badge -->
                    <div class="hidden md:flex items-center px-2.5 py-1 text-xs rounded-md bg-[#00529B]/10 text-[#00529B] font-medium border border-[#00529B]/20">
                        <svg class="w-3.5 h-3.5 mr-1.5 text-[#00529B]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span>{{ session('auth_user.role_name', 'Super Admin') }}</span>
                    </div>

                    <!-- Notifications Dropdown -->
                    <div class="relative" @click.away="notificationOpen = false">
                        <button @click="notificationOpen = !notificationOpen" class="relative p-2 text-slate-400 rounded-full hover:text-slate-600 hover:bg-slate-100">
                            <span class="sr-only">Notifikasi</span>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            <span class="absolute top-1 right-1 w-2 h-2 rounded-full bg-[#D71920]"></span>
                        </button>

                        <div x-show="notificationOpen" 
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute right-0 z-50 w-80 mt-2 bg-white rounded-lg shadow-lg border border-slate-200 py-2" x-cloak>
                            <div class="px-4 py-2 border-b border-slate-100 font-semibold text-xs text-slate-700 flex justify-between items-center">
                                <span>Pemberitahuan HSSE</span>
                                <span class="text-[10px] bg-red-100 text-red-600 px-1.5 py-0.5 rounded font-bold">2 Baru</span>
                            </div>
                            <div class="divide-y divide-slate-100 max-h-72 overflow-y-auto">
                                <a href="{{ route('findings.index') }}" class="block px-4 py-3 hover:bg-slate-50 transition">
                                    <div class="text-xs font-semibold text-red-600">Temuan Kritis: Eyewash TPS-002</div>
                                    <div class="text-[11px] text-slate-500 mt-0.5">Air keruh berlumut membutuhkan tindakan segera.</div>
                                    <div class="text-[9px] text-slate-400 mt-1">2 jam yang lalu</div>
                                </a>
                                <a href="{{ route('schedules.index') }}" class="block px-4 py-3 hover:bg-slate-50 transition">
                                    <div class="text-xs font-semibold text-amber-600">Jadwal Mendatang: Hydrant Tank T-01</div>
                                    <div class="text-[11px] text-slate-500 mt-0.5">Uji rutin flushing besok pagi pukul 08:00 WIB.</div>
                                    <div class="text-[9px] text-slate-400 mt-1">Hari ini</div>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- User Profile Dropdown -->
                    <div class="relative" @click.away="profileOpen = false">
                        <button @click="profileOpen = !profileOpen" class="flex items-center space-x-2.5 p-1 rounded-full hover:bg-slate-100 focus:outline-none">
                            <div class="flex items-center justify-center w-8 h-8 font-semibold text-white rounded-full bg-[#00529B] text-xs">
                                {{ strtoupper(substr(session('auth_user.name', 'Super Admin'), 0, 2)) }}
                            </div>
                            <span class="hidden text-xs font-medium text-slate-700 md:block max-w-[130px] truncate">
                                {{ session('auth_user.name', 'Super Admin') }}
                            </span>
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="profileOpen" 
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute right-0 z-50 w-56 mt-2 bg-white rounded-lg shadow-lg border border-slate-200 py-1" x-cloak>
                            <div class="px-4 py-2 border-b border-slate-100">
                                <div class="text-xs font-semibold text-slate-800">{{ session('auth_user.name', 'Super Admin') }}</div>
                                <div class="text-[11px] text-slate-500 truncate">{{ session('auth_user.email', 'superadmin@pertamina.demo') }}</div>
                                <div class="text-[10px] text-emerald-600 font-medium mt-0.5">{{ session('auth_user.jabatan', 'IT & HSSE System') }}</div>
                            </div>
                            <a href="{{ route('dashboard') }}" class="block px-4 py-2 text-xs text-slate-700 hover:bg-slate-50">Profil Pengguna</a>
                            <a href="{{ route('audit-logs.index') }}" class="block px-4 py-2 text-xs text-slate-700 hover:bg-slate-50">Log Aktivitas Saya</a>
                            <div class="border-t border-slate-100 my-1"></div>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-xs text-red-600 hover:bg-red-50 flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                    Keluar Sistem
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <!-- FLASH ALERTS -->
            <div class="px-4 sm:px-6 pt-4">
                @if(session('success'))
                    <div class="p-3 mb-3 text-xs text-emerald-800 rounded-lg bg-emerald-50 border border-emerald-200 flex items-center justify-between" x-data="{ show: true }" x-show="show">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span>{{ session('success') }}</span>
                        </div>
                        <button @click="show = false" class="text-emerald-500 hover:text-emerald-800">&times;</button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="p-3 mb-3 text-xs text-red-800 rounded-lg bg-red-50 border border-red-200 flex items-center justify-between" x-data="{ show: true }" x-show="show">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2 text-red-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                            <span>{{ session('error') }}</span>
                        </div>
                        <button @click="show = false" class="text-red-500 hover:text-red-800">&times;</button>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="p-3 mb-3 text-xs text-amber-800 rounded-lg bg-amber-50 border border-amber-200 flex items-center justify-between" x-data="{ show: true }" x-show="show">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2 text-amber-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            <span>{{ session('warning') }}</span>
                        </div>
                        <button @click="show = false" class="text-amber-500 hover:text-amber-800">&times;</button>
                    </div>
                @endif
            </div>

            <!-- MAIN VIEW BODY -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6">
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
