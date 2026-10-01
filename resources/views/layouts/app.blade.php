<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SIM-MAGANG') - Badan Pusat Statistik Kabupaten Karanganyar</title>

    <!-- Favicon Resmi BPS -->
    <link rel="icon" type="image/png" href="{{ asset('images/bps-logo.png') }}">

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        bps: {
                            50: '#f0f7ff',
                            100: '#e0effe',
                            200: '#b9dffd',
                            300: '#7cc4fb',
                            400: '#36a5f7',
                            500: '#0284c7',
                            600: '#0369a1',
                            700: '#075985',
                            800: '#1e3a8a',
                            900: '#0f172a',
                            navy: '#002855',
                            blue: '#0055A5',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Leaflet CSS & JS for OpenStreetMap -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <!-- Alpine.js for UI state management -->
    <script defer src="https://unpkg.com/alpinejs@3.14.3/dist/cdn.min.js"></script>

    <!-- Chart.js for analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        [x-cloak] {
            display: none !important;
        }

        /* SweetAlert2 Styling to Match Professional Government Theme */
        .swal2-popup {
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            border-radius: 1rem !important;
            padding: 1.5rem !important;
            border: 1px solid #e2e8f0 !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
        }

        .swal2-title {
            font-size: 1.125rem !important;
            font-weight: 700 !important;
            color: #0f172a !important;
        }

        .swal2-html-container {
            font-size: 0.875rem !important;
            color: #475569 !important;
        }

        .swal2-confirm {
            border-radius: 0.5rem !important;
            padding: 0.5rem 1.25rem !important;
            font-weight: 600 !important;
            font-size: 0.875rem !important;
            background-color: #1e3a8a !important;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
        }

        .swal2-cancel {
            border-radius: 0.5rem !important;
            padding: 0.5rem 1.25rem !important;
            font-weight: 600 !important;
            font-size: 0.875rem !important;
        }
    </style>
    @stack('styles')
</head>

<body class="h-full font-sans text-slate-800 antialiased flex flex-col min-h-screen bg-slate-50" x-data="{ sidebarOpen: false }">

    @auth
    <!-- Top Navigation Header (Clean Executive White) -->
    <header class="sticky top-0 z-30 bg-white/95 backdrop-blur-md border-b border-slate-200 shadow-xs">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Brand & Logo -->
                <div class="flex items-center space-x-2 sm:space-x-3">
                    <button @click="sidebarOpen = !sidebarOpen" type="button" aria-label="Buka Menu"
                        class="lg:hidden p-2 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition focus:outline-none">
                        <i data-lucide="menu" class="w-5 h-5"></i>
                    </button>

                    <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('intern.dashboard') }}" class="flex items-center space-x-2.5 sm:space-x-3 group">
                        <img src="{{ asset('images/bps-logo.png') }}" alt="Logo BPS" class="h-7 sm:h-8 w-auto object-contain">
                        <div>
                            <div class="flex items-center gap-1.5 sm:gap-2">
                                <span class="text-xs sm:text-sm font-extrabold tracking-tight text-slate-900 leading-tight">BPS KARANGANYAR</span>
                                <span class="text-[9px] sm:text-[10px] uppercase font-bold tracking-wider px-1.5 sm:px-2 py-0.5 rounded-full {{ auth()->user()->isAdmin() ? 'bg-amber-50 text-amber-800 border border-amber-200' : 'bg-blue-50 text-blue-800 border border-blue-200' }}">
                                    {{ auth()->user()->isAdmin() ? 'PEMBIMBING' : 'MAGANG' }}
                                </span>
                            </div>
                            <div class="text-[10px] sm:text-[11px] text-slate-500 font-medium leading-tight hidden sm:block">
                                SIM-MAGANG • Presensi & Logbook Aktivitas
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Desktop Nav Links (Spacious & Clean on >= lg screens) -->
                <nav class="hidden lg:flex items-center space-x-1">
                    @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="px-2.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 {{ request()->routeIs('admin.dashboard') ? 'bg-blue-50 text-blue-900 font-bold border border-blue-200/80' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        <i data-lucide="layout-dashboard" class="w-3.5 h-3.5"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="{{ route('admin.verifikasi.index') }}" class="px-2.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 relative {{ request()->routeIs('admin.verifikasi.*') ? 'bg-blue-50 text-blue-900 font-bold border border-blue-200/80' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        <i data-lucide="check-check" class="w-3.5 h-3.5"></i>
                        <span>Verifikasi ACC</span>
                        @php
                        $pendingTotal = \App\Models\Attendance::where('approval_status', 'pending')->count();
                        @endphp
                        @if($pendingTotal > 0)
                        <span class="ml-1 px-1.5 py-0.2 text-[10px] font-bold bg-amber-500 text-white rounded-full">{{ $pendingTotal }}</span>
                        @endif
                    </a>
                    <a href="{{ route('admin.rekap.index') }}" class="px-2.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 {{ request()->routeIs('admin.rekap.*') ? 'bg-blue-50 text-blue-900 font-bold border border-blue-200/80' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5"></i>
                        <span>Rekapitulasi</span>
                    </a>
                    <a href="{{ route('admin.interns.index') }}" class="px-2.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 {{ request()->routeIs('admin.interns.*') ? 'bg-blue-50 text-blue-900 font-bold border border-blue-200/80' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        <i data-lucide="users" class="w-3.5 h-3.5"></i>
                        <span>Peserta Magang</span>
                    </a>
                    <a href="{{ route('admin.evaluasi.index') }}" class="px-2.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 {{ request()->routeIs('admin.evaluasi.*') ? 'bg-blue-50 text-blue-900 font-bold border border-blue-200/80' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        <i data-lucide="award" class="w-3.5 h-3.5"></i>
                        <span>Penilaian Magang</span>
                    </a>
                    <a href="{{ route('admin.holidays.index') }}" class="px-2.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 {{ request()->routeIs('admin.holidays.*') ? 'bg-blue-50 text-blue-900 font-bold border border-blue-200/80' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                        <span>Hari Libur</span>
                    </a>
                    <a href="{{ route('admin.settings.index') }}" class="px-2.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 {{ request()->routeIs('admin.settings.*') ? 'bg-blue-50 text-blue-900 font-bold border border-blue-200/80' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        <i data-lucide="map-pin" class="w-3.5 h-3.5"></i>
                        <span>Titik Kantor</span>
                    </a>
                    @else
                    <a href="{{ route('intern.dashboard') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 {{ request()->routeIs('intern.dashboard') ? 'bg-blue-50 text-blue-900 font-bold border border-blue-200/80' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                        <span>Beranda</span>
                    </a>
                    <a href="{{ route('intern.absen') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 {{ request()->routeIs('intern.absen') ? 'bg-blue-50 text-blue-900 font-bold border border-blue-200/80' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        <i data-lucide="clipboard-check" class="w-4 h-4"></i>
                        <span>Presensi Harian</span>
                    </a>
                    <a href="{{ route('intern.riwayat') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 {{ request()->routeIs('intern.riwayat') ? 'bg-blue-50 text-blue-900 font-bold border border-blue-200/80' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        <i data-lucide="history" class="w-4 h-4"></i>
                        <span>Riwayat Logbook</span>
                    </a>
                    <a href="{{ route('intern.evaluasi') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 {{ request()->routeIs('intern.evaluasi') ? 'bg-blue-50 text-blue-900 font-bold border border-blue-200/80' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        <i data-lucide="award" class="w-4 h-4"></i>
                        <span>Evaluasi Akhir</span>
                    </a>
                    <a href="{{ route('intern.profil') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 {{ request()->routeIs('intern.profil') ? 'bg-blue-50 text-blue-900 font-bold border border-blue-200/80' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        <i data-lucide="user" class="w-4 h-4"></i>
                        <span>Profil Saya</span>
                    </a>
                    @endif
                </nav>

                <!-- Right Section: Live Clock & User Menu -->
                <div class="flex items-center space-x-2 sm:space-x-3">
                    <!-- Live WIB Clock (Visible on 2XL wide screens to avoid crowding) -->
                    <div class="hidden 2xl:flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-100/80 border border-slate-200 text-xs text-slate-600 font-mono">
                        <i data-lucide="clock" class="w-3.5 h-3.5 text-blue-700"></i>
                        <span id="liveWibClock">Memuat waktu...</span>
                    </div>

                    <!-- User Dropdown Menu -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" type="button" class="flex items-center space-x-2 p-1 rounded-lg hover:bg-slate-100 transition focus:outline-none">
                            <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-900 border border-blue-200 flex items-center justify-center font-bold text-xs overflow-hidden flex-shrink-0">
                                @if(auth()->user()->avatar)
                                <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="Avatar" class="w-full h-full object-cover">
                                @else
                                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                                @endif
                            </div>
                            <div class="hidden md:block text-left">
                                <span class="text-xs font-bold block max-w-[130px] truncate text-slate-800 leading-tight">{{ auth()->user()->name }}</span>
                                <span class="text-[10px] text-slate-500 block leading-tight">{{ auth()->user()->isAdmin() ? 'Pembimbing BPS' : 'Peserta Magang' }}</span>
                            </div>
                            <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400"></i>
                        </button>

                        <!-- Dropdown panel -->
                        <div x-show="open" @click.away="open = false" x-cloak
                            class="origin-top-right absolute right-0 mt-2 w-60 rounded-xl shadow-lg bg-white ring-1 ring-slate-900/5 border border-slate-200 divide-y divide-slate-100 z-50 text-slate-700">
                            <div class="p-3.5 bg-slate-50/60 rounded-t-xl">
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Akun Terdaftar</p>
                                <p class="text-xs font-bold text-slate-900 truncate mt-0.5">{{ auth()->user()->name }}</p>
                                <p class="text-[11px] text-blue-800 font-semibold truncate">
                                    {{ auth()->user()->isAdmin() ? 'Administrator & Pembimbing BPS' : (auth()->user()->internProfile->division ?? 'Peserta Magang BPS') }}
                                </p>
                                <p class="text-[10px] text-slate-500 font-mono mt-0.5 truncate">{{ auth()->user()->email }}</p>
                            </div>
                            <div class="py-1">
                                @if(auth()->user()->isIntern())
                                <a href="{{ route('intern.profil') }}" class="flex items-center gap-2 px-4 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-blue-800 transition">
                                    <i data-lucide="user" class="w-3.5 h-3.5 text-slate-400"></i>
                                    <span>Data Profil Akademik</span>
                                </a>
                                @endif
                                <form id="logoutForm" method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="button" onclick="confirmLogout()" class="w-full text-left flex items-center gap-2 px-4 py-2 text-xs font-medium text-rose-600 hover:bg-rose-50 transition">
                                        <i data-lucide="log-out" class="w-3.5 h-3.5 text-rose-500"></i>
                                        <span>Keluar dari Sistem</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Backdrop Overlay -->
        <div x-show="sidebarOpen" x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="lg:hidden fixed inset-0 z-40 bg-slate-900/40 backdrop-blur-xs"
            @click="sidebarOpen = false"></div>

        <!-- Mobile Drawer Navigation (Slide Down with Smooth Styling) -->
        <div x-show="sidebarOpen" x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="-translate-y-2 opacity-0"
            x-transition:enter-end="translate-y-0 opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="translate-y-0 opacity-100"
            x-transition:leave-end="-translate-y-2 opacity-0"
            class="lg:hidden absolute top-16 left-0 right-0 z-50 bg-white border-b border-slate-200 shadow-xl px-4 py-4 space-y-3 divide-y divide-slate-100 max-h-[85vh] overflow-y-auto">

            <!-- User Summary Header in Mobile Drawer -->
            <div class="flex items-center space-x-3 pb-3">
                <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-900 border border-blue-200 flex items-center justify-center font-bold text-sm overflow-hidden flex-shrink-0">
                    @if(auth()->user()->avatar)
                    <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="Avatar" class="w-full h-full object-cover">
                    @else
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    @endif
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-1.5">
                        <p class="text-xs font-bold text-slate-900 truncate">{{ auth()->user()->name }}</p>
                        <span class="text-[9px] uppercase font-bold tracking-wider px-1.5 py-0.2 rounded-full {{ auth()->user()->isAdmin() ? 'bg-amber-50 text-amber-800 border border-amber-200' : 'bg-blue-50 text-blue-800 border border-blue-200' }}">
                            {{ auth()->user()->isAdmin() ? 'PEMBIMBING' : 'MAGANG' }}
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-500 font-mono truncate">{{ auth()->user()->email }}</p>
                </div>
            </div>

            <!-- Live Clock in Mobile Drawer -->
            <div class="pt-3">
                <div class="flex items-center gap-2 px-3 py-2 rounded-lg bg-slate-50 border border-slate-200 text-xs font-mono text-slate-600">
                    <i data-lucide="clock" class="w-4 h-4 text-blue-700 flex-shrink-0"></i>
                    <span class="mobileLiveWibClock">Memuat waktu...</span>
                </div>
            </div>

            <!-- Nav Links List -->
            <div class="pt-3 space-y-1">
                @if(auth()->user()->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-semibold transition {{ request()->routeIs('admin.dashboard') ? 'bg-blue-50 text-blue-900 font-bold border-l-4 border-blue-800' : 'text-slate-700 hover:bg-slate-100' }}">
                    <i data-lucide="layout-dashboard" class="w-4 h-4 text-blue-800"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('admin.verifikasi.index') }}" class="flex items-center justify-between px-3 py-2.5 rounded-lg text-xs font-semibold transition {{ request()->routeIs('admin.verifikasi.*') ? 'bg-blue-50 text-blue-900 font-bold border-l-4 border-blue-800' : 'text-slate-700 hover:bg-slate-100' }}">
                    <div class="flex items-center gap-3">
                        <i data-lucide="check-check" class="w-4 h-4 text-blue-800"></i>
                        <span>Verifikasi ACC Presensi</span>
                    </div>
                    @if($pendingTotal > 0)
                    <span class="px-2 py-0.5 text-[10px] font-bold bg-amber-500 text-white rounded-full">{{ $pendingTotal }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.rekap.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-semibold transition {{ request()->routeIs('admin.rekap.*') ? 'bg-blue-50 text-blue-900 font-bold border-l-4 border-blue-800' : 'text-slate-700 hover:bg-slate-100' }}">
                    <i data-lucide="file-spreadsheet" class="w-4 h-4 text-blue-800"></i>
                    <span>Rekapitulasi Presensi</span>
                </a>
                <a href="{{ route('admin.interns.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-semibold transition {{ request()->routeIs('admin.interns.*') ? 'bg-blue-50 text-blue-900 font-bold border-l-4 border-blue-800' : 'text-slate-700 hover:bg-slate-100' }}">
                    <i data-lucide="users" class="w-4 h-4 text-blue-800"></i>
                    <span>Data Peserta Magang</span>
                </a>
                <a href="{{ route('admin.evaluasi.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-semibold transition {{ request()->routeIs('admin.evaluasi.*') ? 'bg-blue-50 text-blue-900 font-bold border-l-4 border-blue-800' : 'text-slate-700 hover:bg-slate-100' }}">
                    <i data-lucide="award" class="w-4 h-4 text-blue-800"></i>
                    <span>Penilaian Magang</span>
                </a>
                <a href="{{ route('admin.holidays.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-semibold transition {{ request()->routeIs('admin.holidays.*') ? 'bg-blue-50 text-blue-900 font-bold border-l-4 border-blue-800' : 'text-slate-700 hover:bg-slate-100' }}">
                    <i data-lucide="calendar" class="w-4 h-4 text-blue-800"></i>
                    <span>Hari Libur Nasional</span>
                </a>
                <a href="{{ route('admin.settings.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-semibold transition {{ request()->routeIs('admin.settings.*') ? 'bg-blue-50 text-blue-900 font-bold border-l-4 border-blue-800' : 'text-slate-700 hover:bg-slate-100' }}">
                    <i data-lucide="map-pin" class="w-4 h-4 text-blue-800"></i>
                    <span>Titik Lokasi Kantor BPS</span>
                </a>
                @else
                <a href="{{ route('intern.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-semibold transition {{ request()->routeIs('intern.dashboard') ? 'bg-blue-50 text-blue-900 font-bold border-l-4 border-blue-800' : 'text-slate-700 hover:bg-slate-100' }}">
                    <i data-lucide="layout-dashboard" class="w-4 h-4 text-blue-800"></i>
                    <span>Beranda Utama</span>
                </a>
                <a href="{{ route('intern.absen') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-semibold transition {{ request()->routeIs('intern.absen') ? 'bg-blue-50 text-blue-900 font-bold border-l-4 border-blue-800' : 'text-slate-700 hover:bg-slate-100' }}">
                    <i data-lucide="clipboard-check" class="w-4 h-4 text-blue-800"></i>
                    <span>Presensi Harian</span>
                </a>
                <a href="{{ route('intern.riwayat') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-semibold transition {{ request()->routeIs('intern.riwayat') ? 'bg-blue-50 text-blue-900 font-bold border-l-4 border-blue-800' : 'text-slate-700 hover:bg-slate-100' }}">
                    <i data-lucide="history" class="w-4 h-4 text-blue-800"></i>
                    <span>Riwayat Logbook</span>
                </a>
                <a href="{{ route('intern.evaluasi') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-semibold transition {{ request()->routeIs('intern.evaluasi') ? 'bg-blue-50 text-blue-900 font-bold border-l-4 border-blue-800' : 'text-slate-700 hover:bg-slate-100' }}">
                    <i data-lucide="award" class="w-4 h-4 text-blue-800"></i>
                    <span>Evaluasi Akhir</span>
                </a>
                <a href="{{ route('intern.profil') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-semibold transition {{ request()->routeIs('intern.profil') ? 'bg-blue-50 text-blue-900 font-bold border-l-4 border-blue-800' : 'text-slate-700 hover:bg-slate-100' }}">
                    <i data-lucide="user" class="w-4 h-4 text-blue-800"></i>
                    <span>Profil Saya</span>
                </a>
                @endif
            </div>

            <!-- Logout in Mobile Drawer -->
            <div class="pt-3">
                <button type="button" onclick="confirmLogout()" class="w-full flex items-center justify-center gap-2 px-3 py-2.5 rounded-lg text-xs font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 transition border border-rose-200">
                    <i data-lucide="log-out" class="w-4 h-4 text-rose-500"></i>
                    <span>Keluar dari Sistem</span>
                </button>
            </div>
        </div>
    </header>
    @endauth

    <!-- Main Content Body -->
    <main class="flex-1 w-full {{ auth()->check() ? 'max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-5 sm:py-6 pb-24 lg:pb-8' : '' }}">
        @yield('content')
    </main>

    @auth
    @if(auth()->user()->isIntern())
    <!-- Mobile Bottom Navigation Bar for Intern (4 balanced items) -->
    <nav class="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-t border-slate-200 shadow-lg px-2 py-1 flex items-center justify-around">
        <a href="{{ route('intern.dashboard') }}" class="flex flex-col items-center gap-0.5 py-1 px-3 rounded-lg transition {{ request()->routeIs('intern.dashboard') ? 'text-blue-900 font-bold' : 'text-slate-500 hover:text-slate-800' }}">
            <i data-lucide="layout-dashboard" class="w-5 h-5 {{ request()->routeIs('intern.dashboard') ? 'stroke-[2.5]' : '' }}"></i>
            <span class="text-[10px]">Beranda</span>
        </a>

        <a href="{{ route('intern.absen') }}" class="flex flex-col items-center gap-0.5 py-1 px-3 rounded-lg transition {{ request()->routeIs('intern.absen') ? 'text-blue-900 font-bold' : 'text-slate-500 hover:text-slate-800' }}">
            <i data-lucide="clipboard-check" class="w-5 h-5 {{ request()->routeIs('intern.absen') ? 'stroke-[2.5]' : '' }}"></i>
            <span class="text-[10px]">Presensi</span>
        </a>

        <a href="{{ route('intern.riwayat') }}" class="flex flex-col items-center gap-0.5 py-1 px-3 rounded-lg transition {{ request()->routeIs('intern.riwayat') ? 'text-blue-900 font-bold' : 'text-slate-500 hover:text-slate-800' }}">
            <i data-lucide="history" class="w-5 h-5 {{ request()->routeIs('intern.riwayat') ? 'stroke-[2.5]' : '' }}"></i>
            <span class="text-[10px]">Riwayat</span>
        </a>

        <a href="{{ route('intern.profil') }}" class="flex flex-col items-center gap-0.5 py-1 px-3 rounded-lg transition {{ request()->routeIs('intern.profil') ? 'text-blue-900 font-bold' : 'text-slate-500 hover:text-slate-800' }}">
            <i data-lucide="user" class="w-5 h-5 {{ request()->routeIs('intern.profil') ? 'stroke-[2.5]' : '' }}"></i>
            <span class="text-[10px]">Profil</span>
        </a>
    </nav>
    @else
    <!-- Mobile Bottom Navigation Bar for Admin (Fast Actions) -->
    <nav class="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-t border-slate-200 shadow-lg px-2 py-1 flex items-center justify-around">
        <a href="{{ route('admin.dashboard') }}" class="flex flex-col items-center gap-0.5 py-1 px-3 rounded-lg transition {{ request()->routeIs('admin.dashboard') ? 'text-blue-900 font-bold' : 'text-slate-500 hover:text-slate-800' }}">
            <i data-lucide="layout-dashboard" class="w-5 h-5 {{ request()->routeIs('admin.dashboard') ? 'stroke-[2.5]' : '' }}"></i>
            <span class="text-[10px]">Dashboard</span>
        </a>

        <a href="{{ route('admin.verifikasi.index') }}" class="flex flex-col items-center gap-0.5 py-1 px-3 rounded-lg relative transition {{ request()->routeIs('admin.verifikasi.*') ? 'text-blue-900 font-bold' : 'text-slate-500 hover:text-slate-800' }}">
            <i data-lucide="check-check" class="w-5 h-5 {{ request()->routeIs('admin.verifikasi.*') ? 'stroke-[2.5]' : '' }}"></i>
            <span class="text-[10px]">Verifikasi</span>
            @php
            $pendingMobile = \App\Models\Attendance::where('approval_status', 'pending')->count();
            @endphp
            @if($pendingMobile > 0)
            <span class="absolute top-0 right-2 w-2 h-2 rounded-full bg-amber-500 ring-2 ring-white"></span>
            @endif
        </a>

        <a href="{{ route('admin.rekap.index') }}" class="flex flex-col items-center gap-0.5 py-1 px-3 rounded-lg transition {{ request()->routeIs('admin.rekap.*') ? 'text-blue-900 font-bold' : 'text-slate-500 hover:text-slate-800' }}">
            <i data-lucide="file-spreadsheet" class="w-5 h-5 {{ request()->routeIs('admin.rekap.*') ? 'stroke-[2.5]' : '' }}"></i>
            <span class="text-[10px]">Rekap</span>
        </a>

        <button @click="sidebarOpen = !sidebarOpen" type="button" class="flex flex-col items-center gap-0.5 py-1 px-3 rounded-lg text-slate-500 hover:text-slate-800 transition">
            <i data-lucide="menu" class="w-5 h-5"></i>
            <span class="text-[10px]">Menu</span>
        </button>
    </nav>
    @endif
    @endauth

    @auth
    <!-- Footer Resmi BPS -->
    <footer class="bg-white border-t border-slate-200 py-4 mt-auto text-slate-500 text-xs hidden md:block">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-2">
            <div class="flex items-center space-x-2">
                <img src="{{ asset('images/bps-logo.png') }}" alt="Logo BPS" class="h-4 w-auto">
                <span class="font-bold text-slate-700">Badan Pusat Statistik Kabupaten Karanganyar</span>
                <span class="text-slate-300">•</span>
                <span>Komplek Perkantoran Cangakan, Karanganyar 57712</span>
            </div>
            <div>
                &copy; {{ date('Y') }} SIM-MAGANG BPS • Portal Presensi Terpadu & Logbook Magang
            </div>
        </div>
    </footer>
    @endauth

    <!-- Inisialisasi Lucide Icons & SweetAlert2 Flash Notification Handler -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Render Lucide Icons
            if (window.lucide) {
                lucide.createIcons();
            }

            // Live Digital Clock WIB (Desktop and Mobile)
            function updateClock() {
                const now = new Date();
                const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                const dayName = days[now.getDay()];
                const dateNum = now.getDate();
                const monthName = months[now.getMonth()];
                const year = now.getFullYear();
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                const seconds = String(now.getSeconds()).padStart(2, '0');
                const formattedTime = `${dayName}, ${dateNum} ${monthName} ${year} • ${hours}:${minutes}:${seconds} WIB`;

                const clockEl = document.getElementById('liveWibClock');
                if (clockEl) clockEl.textContent = formattedTime;

                document.querySelectorAll('.mobileLiveWibClock').forEach(el => {
                    el.textContent = formattedTime;
                });
            }
            setInterval(updateClock, 1000);
            updateClock();
        });

        // Global SweetAlert Confirm Helper
        function confirmAction({
            title,
            text,
            icon = 'question',
            confirmText = 'Ya, Lanjutkan',
            confirmColor = '#1e3a8a',
            onConfirm
        }) {
            Swal.fire({
                title: title,
                text: text,
                icon: icon,
                showCancelButton: true,
                confirmButtonColor: confirmColor,
                cancelButtonColor: '#94a3b8',
                confirmButtonText: confirmText,
                cancelButtonText: 'Batal',
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed && typeof onConfirm === 'function') {
                    onConfirm();
                }
            });
        }

        // Global Logout Confirmation
        function confirmLogout() {
            Swal.fire({
                title: 'Konfirmasi Keluar',
                text: 'Apakah Anda yakin ingin keluar dari sistem SIM-MAGANG BPS?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#e11d48',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Ya, Keluar',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('logoutForm').submit();
                }
            });
        }
    </script>

    <!-- Global Toast Alert dari Flash Session -->
    @if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: "{{ session('success') }}",
                showConfirmButton: false,
                timer: 3500,
                timerProgressBar: true
            });
        });
    </script>
    @endif

    @if(session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'error',
                title: "{{ session('error') }}",
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true
            });
        });
    </script>
    @endif

    @if(session('info'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'info',
                title: "{{ session('info') }}",
                showConfirmButton: false,
                timer: 3500,
                timerProgressBar: true
            });
        });
    </script>
    @endif

    @stack('scripts')
</body>

</html>