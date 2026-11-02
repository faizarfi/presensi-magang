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
                            50: '#f0f5fa',
                            100: '#e1ecf6',
                            200: '#c5dcee',
                            300: '#9ac4e2',
                            400: '#67a6d3',
                            500: '#3f88c3',
                            600: '#2b6ea9',
                            700: '#0055a5',  // Official BPS Primary Blue
                            800: '#003b73',  // Darker BPS Blue
                            900: '#002855',  // Official BPS Primary Navy
                            dark: '#001a38',
                            navy: '#002855',
                            blue: '#0055a5',
                            green: '#008850', // Official BPS Leaf Green
                            orange: '#f58220', // Official BPS Data Orange
                        },
                        blue: {
                            50: '#f0f5fa',
                            100: '#e1ecf6',
                            200: '#c5dcee',
                            300: '#9ac4e2',
                            400: '#67a6d3',
                            500: '#3f88c3',
                            600: '#2b6ea9',
                            700: '#0055a5',  // Official BPS Primary Blue
                            800: '#003b73',  // Darker BPS Blue
                            900: '#002855',  // Official BPS Primary Navy
                            950: '#001a38',
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

        /* SweetAlert2 Styling to Match Professional Government Theme (BPS Karanganyar) */
        .swal2-popup {
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            border-radius: 1.25rem !important;
            padding: 1.5rem !important;
            border: 1px solid #e2e8f0 !important;
            box-shadow: 0 10px 25px -5px rgba(0, 40, 85, 0.08), 0 8px 10px -6px rgba(0, 40, 85, 0.06) !important;
        }

        .swal2-title {
            font-size: 1.125rem !important;
            font-weight: 700 !important;
            color: #002855 !important;
        }

        .swal2-html-container {
            font-size: 0.875rem !important;
            color: #475569 !important;
        }

        .swal2-confirm {
            border-radius: 0.75rem !important;
            padding: 0.6rem 1.35rem !important;
            font-weight: 700 !important;
            font-size: 0.875rem !important;
            background-color: #002855 !important;
            box-shadow: 0 2px 4px 0 rgba(0, 40, 85, 0.15) !important;
        }

        .swal2-cancel {
            border-radius: 0.75rem !important;
            padding: 0.6rem 1.35rem !important;
            font-weight: 600 !important;
            font-size: 0.875rem !important;
        }
    </style>
    @stack('styles')
</head>

<body class="h-full font-sans text-slate-800 antialiased bg-slate-50 min-h-screen flex flex-col"
      x-data="{ 
          sidebarOpen: window.innerWidth >= 1024 ? (localStorage.getItem('sim_sidebar_open') !== 'false') : false,
          toggleSidebar() {
              this.sidebarOpen = !this.sidebarOpen;
              if (window.innerWidth >= 1024) {
                  localStorage.setItem('sim_sidebar_open', this.sidebarOpen);
              }
              this.$nextTick(() => {
                  if (typeof lucide !== 'undefined') lucide.createIcons();
                  window.dispatchEvent(new Event('resize'));
              });
          }
      }">

    @auth
    @php
    $authUser = auth()->user();
    $pendingTotal = \App\Models\Attendance::where('approval_status', 'pending')->count();
    $headerTodayAtt = null;
    $notifCount = 0;
    if ($authUser && $authUser->isIntern()) {
        $headerTodayAtt = \App\Models\Attendance::where('user_id', $authUser->id)->whereDate('date', today())->first();
        if (!$headerTodayAtt && !now()->isWeekend()) {
            $notifCount = 1;
        } elseif ($headerTodayAtt && !$headerTodayAtt->check_out_time && now()->format('H:i') >= '15:00') {
            $notifCount = 1;
        }
    } elseif ($authUser && $authUser->isAdmin()) {
        $notifCount = $pendingTotal;
    }
    @endphp

    <div class="min-h-screen flex flex-1">
        <!-- Mobile Sidebar Backdrop Overlay -->
        <div x-show="sidebarOpen" x-cloak
            x-transition:enter="transition-opacity ease-linear duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-40 lg:hidden"
            @click="sidebarOpen = false"></div>

        <!-- ========================================================
             SIDEBAR (Fixed di Layar, Tetap Diam Saat Halaman Di-scroll)
             ======================================================== -->
        <aside
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-50 w-64 xl:w-72 bg-white border-r border-slate-200 flex flex-col transition-transform duration-300 ease-in-out h-screen shrink-0">
            <!-- Institutional Accent Strip (BPS Official Gradient: Navy -> Blue -> Green) -->
            <div class="h-1 bg-gradient-to-r from-bps-900 via-bps-700 to-bps-green w-full shrink-0"></div>
            
            <!-- Brand & Logo Header -->
            <div class="h-16 px-4 sm:px-5 border-b border-slate-200 flex items-center justify-between shrink-0 bg-white">
                <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('intern.dashboard') }}" class="flex items-center space-x-3 group min-w-0">
                    <img src="{{ asset('images/bps-logo.png') }}" alt="Logo BPS" class="h-8 w-auto object-contain shrink-0">
                    <div class="min-w-0">
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs sm:text-sm font-extrabold tracking-tight text-slate-900 truncate leading-tight">BPS KARANGANYAR</span>
                        </div>
                        <div class="flex items-center gap-1.5 mt-0.5">
                            <span class="text-[9px] uppercase font-bold tracking-wider px-1.5 py-0.2 rounded-full shrink-0 {{ auth()->user()->isAdmin() ? 'bg-amber-50 text-amber-900 border border-amber-200' : 'bg-bps-50 text-bps-900 border border-bps-200' }}">
                                {{ auth()->user()->isAdmin() ? 'PEMBIMBING' : 'MAGANG' }}
                            </span>
                            <span class="text-[10px] text-slate-400 font-medium truncate">SIM-MAGANG</span>
                        </div>
                    </div>
                </a>

                <!-- Close / Toggle Button di Header Sidebar -->
                <button @click="toggleSidebar()" type="button" aria-label="Tutup Menu" title="Sembunyikan Sidebar"
                    class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition focus:outline-none cursor-pointer">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Sidebar Navigation Links Scroll Area -->
            <div class="flex-1 overflow-y-auto px-3.5 py-4 space-y-6">
                @if(auth()->user()->isAdmin())
                <!-- Group 1: Menu Utama Admin -->
                <div>
                    <div class="px-3 mb-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                        Menu Utama
                    </div>
                    <nav class="space-y-1">
                        <a href="{{ route('admin.dashboard') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-bps-50 text-bps-900 font-bold border border-bps-200/90 shadow-2xs' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}">
                            <i data-lucide="layout-dashboard" class="w-4 h-4 {{ request()->routeIs('admin.dashboard') ? 'text-bps-700 stroke-[2.2]' : 'text-slate-400' }}"></i>
                            <span class="truncate">Dashboard</span>
                        </a>

                        <a href="{{ route('admin.verifikasi.index') }}"
                           class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('admin.verifikasi.*') ? 'bg-bps-50 text-bps-900 font-bold border border-bps-200/90 shadow-2xs' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}">
                            <div class="flex items-center gap-3 min-w-0">
                                <i data-lucide="check-check" class="w-4 h-4 {{ request()->routeIs('admin.verifikasi.*') ? 'text-bps-700 stroke-[2.2]' : 'text-slate-400' }}"></i>
                                <span class="truncate">Verifikasi ACC</span>
                            </div>
                            @if($pendingTotal > 0)
                            <span class="px-2 py-0.5 text-[10px] font-bold bg-amber-500 text-white rounded-full shadow-2xs">
                                {{ $pendingTotal }}
                            </span>
                            @endif
                        </a>

                        <a href="{{ route('admin.rekap.index') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('admin.rekap.*') ? 'bg-bps-50 text-bps-900 font-bold border border-bps-200/90 shadow-2xs' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}">
                            <i data-lucide="file-spreadsheet" class="w-4 h-4 {{ request()->routeIs('admin.rekap.*') ? 'text-bps-700 stroke-[2.2]' : 'text-slate-400' }}"></i>
                            <span class="truncate">Rekapitulasi</span>
                        </a>

                        <a href="{{ route('admin.interns.index') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('admin.interns.*') ? 'bg-bps-50 text-bps-900 font-bold border border-bps-200/90 shadow-2xs' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}">
                            <i data-lucide="users" class="w-4 h-4 {{ request()->routeIs('admin.interns.*') ? 'text-bps-700 stroke-[2.2]' : 'text-slate-400' }}"></i>
                            <span class="truncate">Peserta Magang</span>
                        </a>

                        <a href="{{ route('admin.evaluasi.index') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('admin.evaluasi.*') ? 'bg-bps-50 text-bps-900 font-bold border border-bps-200/90 shadow-2xs' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}">
                            <i data-lucide="award" class="w-4 h-4 {{ request()->routeIs('admin.evaluasi.*') ? 'text-bps-700 stroke-[2.2]' : 'text-slate-400' }}"></i>
                            <span class="truncate">Penilaian Magang</span>
                        </a>
                    </nav>
                </div>

                <!-- Group 2: Pengaturan Sistem Admin -->
                <div>
                    <div class="px-3 mb-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                        Pengaturan & Master
                    </div>
                    <nav class="space-y-1">
                        <a href="{{ route('admin.holidays.index') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('admin.holidays.*') ? 'bg-bps-50 text-bps-900 font-bold border border-bps-200/90 shadow-2xs' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}">
                            <i data-lucide="calendar" class="w-4 h-4 {{ request()->routeIs('admin.holidays.*') ? 'text-bps-700 stroke-[2.2]' : 'text-slate-400' }}"></i>
                            <span class="truncate">Hari Libur</span>
                        </a>

                        <a href="{{ route('admin.settings.index') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('admin.settings.*') ? 'bg-bps-50 text-bps-900 font-bold border border-bps-200/90 shadow-2xs' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}">
                            <i data-lucide="map-pin" class="w-4 h-4 {{ request()->routeIs('admin.settings.*') ? 'text-bps-700 stroke-[2.2]' : 'text-slate-400' }}"></i>
                            <span class="truncate">Titik Kantor</span>
                        </a>
                    </nav>
                </div>
                @else
                <!-- Group 1: Menu Utama Intern -->
                <div>
                    <div class="px-3 mb-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                        Menu Utama
                    </div>
                    <nav class="space-y-1">
                        <a href="{{ route('intern.dashboard') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('intern.dashboard') ? 'bg-bps-50 text-bps-900 font-bold border border-bps-200/90 shadow-2xs' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}">
                            <i data-lucide="layout-dashboard" class="w-4 h-4 {{ request()->routeIs('intern.dashboard') ? 'text-bps-700 stroke-[2.2]' : 'text-slate-400' }}"></i>
                            <span class="truncate">Beranda</span>
                        </a>

                        <a href="{{ route('intern.absen') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('intern.absen') ? 'bg-bps-50 text-bps-900 font-bold border border-bps-200/90 shadow-2xs' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}">
                            <i data-lucide="clipboard-check" class="w-4 h-4 {{ request()->routeIs('intern.absen') ? 'text-bps-700 stroke-[2.2]' : 'text-slate-400' }}"></i>
                            <span class="truncate">Presensi Harian</span>
                        </a>

                        <a href="{{ route('intern.riwayat') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('intern.riwayat') ? 'bg-bps-50 text-bps-900 font-bold border border-bps-200/90 shadow-2xs' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}">
                            <i data-lucide="history" class="w-4 h-4 {{ request()->routeIs('intern.riwayat') ? 'text-bps-700 stroke-[2.2]' : 'text-slate-400' }}"></i>
                            <span class="truncate">Riwayat Logbook</span>
                        </a>

                        <a href="{{ route('intern.evaluasi') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('intern.evaluasi') ? 'bg-bps-50 text-bps-900 font-bold border border-bps-200/90 shadow-2xs' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}">
                            <i data-lucide="award" class="w-4 h-4 {{ request()->routeIs('intern.evaluasi') ? 'text-bps-700 stroke-[2.2]' : 'text-slate-400' }}"></i>
                            <span class="truncate">Evaluasi Akhir</span>
                        </a>
                    </nav>
                </div>

                <!-- Group 2: Akun & Profil Intern -->
                <div>
                    <div class="px-3 mb-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                        Akun & Profil
                    </div>
                    <nav class="space-y-1">
                        <a href="{{ route('intern.profil') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('intern.profil') ? 'bg-bps-50 text-bps-900 font-bold border border-bps-200/90 shadow-2xs' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}">
                            <i data-lucide="user" class="w-4 h-4 {{ request()->routeIs('intern.profil') ? 'text-bps-700 stroke-[2.2]' : 'text-slate-400' }}"></i>
                            <span class="truncate">Profil Saya</span>
                        </a>
                    </nav>
                </div>
                @endif
            </div>

            <!-- Sidebar User Card at Bottom -->
            <div class="p-3.5 border-t border-slate-200 bg-slate-50/80 shrink-0">
                <div class="flex items-center justify-between gap-2.5">
                    <div class="flex items-center space-x-2.5 min-w-0">
                        <div class="w-8 h-8 rounded-full bg-bps-100 text-bps-900 border border-bps-200 flex items-center justify-center font-bold text-xs shrink-0 overflow-hidden shadow-2xs">
                            @if(auth()->user()->avatar)
                            <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="Avatar" class="w-full h-full object-cover">
                            @else
                            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-bold text-slate-900 truncate leading-tight">{{ auth()->user()->name }}</p>
                            <p class="text-[10px] text-slate-500 truncate leading-tight mt-0.5">
                                {{ auth()->user()->isAdmin() ? 'Pembimbing BPS' : (auth()->user()->internProfile->division ?? 'Peserta Magang') }}
                            </p>
                        </div>
                    </div>
                    <button type="button" onclick="confirmLogout()" title="Keluar dari Sistem"
                        class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition shrink-0">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>
        </aside>

        <!-- ========================================================
             MAIN CONTENT WRAPPER (Menyesuaikan Otomatis Saat Sidebar Dibuka/Ditutup)
             ======================================================== -->
        <div :class="sidebarOpen ? 'lg:pl-64 xl:pl-72' : 'lg:pl-0'"
             class="flex-1 flex flex-col min-w-0 min-h-screen transition-all duration-300 ease-in-out">
            <!-- Institutional Accent Strip (BPS Official Gradient: Navy -> Blue -> Green) -->
            <div class="h-1 bg-gradient-to-r from-bps-900 via-bps-700 to-bps-green w-full shrink-0 sticky top-0 z-40"></div>

            <!-- Top Header Bar -->
            <header class="sticky top-1 z-30 bg-white/95 backdrop-blur-md border-b border-slate-200 shadow-xs h-16 flex items-center justify-between px-3 sm:px-6 lg:px-8">
                <!-- Left: Hamburger toggle button (Desktop & Mobile) + Context Title -->
                <div class="flex items-center space-x-3">
                    <button @click="toggleSidebar()" type="button" aria-label="Buka/Tutup Menu" title="Buka/Tutup Sidebar"
                        class="p-2 rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition focus:outline-none cursor-pointer flex items-center justify-center">
                        <i data-lucide="menu" class="w-5 h-5"></i>
                    </button>

                    <!-- Brand Logo when sidebar is closed -->
                    <div x-show="!sidebarOpen" class="flex items-center space-x-2">
                        <img src="{{ asset('images/bps-logo.png') }}" alt="Logo BPS" class="h-6 w-auto">
                        <span class="text-xs font-extrabold text-slate-900 tracking-tight">BPS KARANGANYAR</span>
                    </div>

                    <!-- Desktop Context Title -->
                    <div class="hidden lg:flex items-center space-x-2 text-xs text-slate-500">
                        <span class="inline-flex items-center gap-1.5 font-bold text-slate-800">
                            <i data-lucide="building-2" class="w-3.5 h-3.5 text-bps-700"></i>
                            <span>Badan Pusat Statistik Kab. Karanganyar</span>
                        </span>
                        <span class="text-slate-300">•</span>
                        <span class="text-slate-500 font-medium">SIM-MAGANG • Presensi GPS & Logbook Digital Terpadu</span>
                    </div>
                </div>

                <!-- Right Section: Live WIB Clock, Notifikasi & User Dropdown -->
                <div class="flex items-center space-x-1.5 sm:space-x-3">
                    <!-- Live WIB Clock Widget -->
                    <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-100/90 border border-slate-200 text-xs text-slate-700 font-mono shadow-2xs">
                        <i data-lucide="clock" class="w-3.5 h-3.5 text-bps-700"></i>
                        <span id="liveWibClock">Memuat waktu...</span>
                    </div>

                    <!-- Notification Bell Dropdown -->
                    <div class="relative" x-data="{ notifOpen: false }">
                        <button @click="notifOpen = !notifOpen" type="button" aria-label="Notifikasi"
                                class="relative p-2 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition focus:outline-none">
                            <i data-lucide="bell" class="w-4 h-4"></i>
                            @if($notifCount > 0)
                            <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-rose-500 rounded-full ring-2 ring-white"></span>
                            @endif
                        </button>

                        <!-- Notification Dropdown Panel -->
                        <div x-show="notifOpen" @click.away="notifOpen = false" x-cloak
                             class="origin-top-right absolute right-0 mt-2 w-80 sm:w-88 rounded-xl shadow-lg bg-white ring-1 ring-slate-900/5 border border-slate-200 divide-y divide-slate-100 z-50 text-slate-700">
                            <div class="p-3.5 bg-slate-50/80 rounded-t-xl flex items-center justify-between">
                                <div>
                                    <h3 class="text-xs font-bold text-slate-900">Notifikasi &amp; Status</h3>
                                    <p class="text-[10px] text-slate-500">SIM-MAGANG BPS Karanganyar</p>
                                </div>
                                @if($notifCount > 0)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700 border border-rose-200">
                                    {{ $notifCount }} Perlu Aksi
                                </span>
                                @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-100 text-emerald-700 border border-emerald-200">
                                    Sesuai Jadwal
                                </span>
                                @endif
                            </div>

                            <div class="p-3 space-y-2.5 max-h-80 overflow-y-auto">
                                @if($authUser->isIntern())
                                    @if(!$headerTodayAtt)
                                        <div class="p-3 rounded-lg bg-amber-50/80 border border-amber-200 text-xs space-y-1.5">
                                            <div class="flex items-center gap-1.5 font-bold text-amber-900">
                                                <i data-lucide="alert-circle" class="w-4 h-4 text-amber-600 shrink-0"></i>
                                                <span>Belum Presensi Datang Hari Ini</span>
                                            </div>
                                            <p class="text-[11px] text-amber-800 leading-relaxed">
                                                Presensi masuk kantor BPS dibuka pukul <strong>{{ \App\Models\Setting::get('morning_start_time', '07:30') }} - {{ \App\Models\Setting::get('morning_end_time', '08:00') }} WIB</strong>. Segera lakukan presensi kehadiran Anda.
                                            </p>
                                            <a href="{{ route('intern.absen') }}" class="inline-flex items-center gap-1 font-bold text-bps-700 hover:text-bps-900 text-[11px] pt-0.5">
                                                <span>Isi Presensi Sekarang</span>
                                                <i data-lucide="arrow-right" class="w-3 h-3"></i>
                                            </a>
                                        </div>
                                    @else
                                        <div class="p-3 rounded-lg bg-bps-50/70 border border-bps-200 text-xs space-y-2">
                                            <div class="flex items-center justify-between">
                                                <span class="font-bold text-slate-900 flex items-center gap-1.5">
                                                    <i data-lucide="check-circle" class="w-3.5 h-3.5 text-bps-700"></i>
                                                    <span>Presensi Hari Ini</span>
                                                </span>
                                                <span class="text-[10px] font-bold px-2 py-0.5 rounded border {{ $headerTodayAtt->approval_badge_class }}">
                                                    {{ $headerTodayAtt->approval_status === 'approved' ? 'ACC Disetujui' : ($headerTodayAtt->approval_status === 'rejected' ? 'Ditolak' : 'Menunggu ACC') }}
                                                </span>
                                            </div>
                                            <div class="grid grid-cols-2 gap-2 text-[11px] text-slate-600 bg-white p-2 rounded border border-bps-100">
                                                <div>
                                                    <span class="text-[9px] uppercase font-bold text-slate-400 block">Jam Masuk</span>
                                                    <span class="font-mono font-bold text-slate-800">{{ $headerTodayAtt->check_in_time ? substr($headerTodayAtt->check_in_time, 0, 5) . ' WIB' : '-' }}</span>
                                                </div>
                                                <div>
                                                    <span class="text-[9px] uppercase font-bold text-slate-400 block">Jam Pulang</span>
                                                    <span class="font-mono font-bold text-slate-800">{{ $headerTodayAtt->check_out_time ? substr($headerTodayAtt->check_out_time, 0, 5) . ' WIB' : 'Belum Pulang' }}</span>
                                                </div>
                                            </div>
                                            @if(!$headerTodayAtt->check_out_time)
                                                <a href="{{ route('intern.absen') }}" class="inline-flex items-center gap-1 font-bold text-bps-700 hover:text-bps-900 text-[11px]">
                                                    <span>Lakukan Presensi Pulang (Check-Out)</span>
                                                    <i data-lucide="arrow-right" class="w-3 h-3"></i>
                                                </a>
                                            @endif
                                        </div>
                                    @endif
                                @else
                                    @if($pendingTotal > 0)
                                        <div class="p-3 rounded-lg bg-amber-50/80 border border-amber-200 text-xs space-y-1.5">
                                            <div class="flex items-center gap-1.5 font-bold text-amber-900">
                                                <i data-lucide="clock" class="w-4 h-4 text-amber-600 shrink-0"></i>
                                                <span>{{ $pendingTotal }} Presensi Menunggu ACC</span>
                                            </div>
                                            <p class="text-[11px] text-amber-800 leading-relaxed">
                                                Ada permohonan presensi/izin peserta magang yang memerlukan verifikasi pembimbing.
                                            </p>
                                            <a href="{{ route('admin.verifikasi.index') }}" class="inline-flex items-center gap-1 font-bold text-bps-700 hover:text-bps-900 text-[11px] pt-0.5">
                                                <span>Tinjau Presensi Sekarang</span>
                                                <i data-lucide="arrow-right" class="w-3 h-3"></i>
                                            </a>
                                        </div>
                                    @else
                                        <div class="p-3 text-center text-slate-500 text-xs">
                                            <i data-lucide="check-check" class="w-5 h-5 mx-auto mb-1 text-emerald-600 opacity-90"></i>
                                            <p class="font-bold text-slate-800">Semua Presensi Telah Ditinjau</p>
                                            <p class="text-[11px] text-slate-500">Tidak ada antrean ACC tertunda saat ini.</p>
                                        </div>
                                    @endif
                                @endif
                            </div>

                            <div class="p-2 bg-slate-50/50 rounded-b-xl text-center">
                                <a href="{{ $authUser->isIntern() ? route('intern.riwayat') : route('admin.verifikasi.index') }}"
                                   class="text-[11px] font-semibold text-bps-700 hover:text-bps-900 transition">
                                    {{ $authUser->isIntern() ? 'Lihat Semua Riwayat Presensi →' : 'Buka Halaman Presensi →' }}
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- User Dropdown Menu -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" type="button" class="flex items-center space-x-2 p-1.5 rounded-lg hover:bg-slate-100 transition focus:outline-none">
                            <div class="w-8 h-8 rounded-full bg-bps-100 text-bps-900 border border-bps-200 flex items-center justify-center font-bold text-xs overflow-hidden shrink-0 shadow-2xs">
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
                            class="origin-top-right absolute right-0 mt-2 w-64 rounded-xl shadow-lg bg-white ring-1 ring-slate-900/5 border border-slate-200 divide-y divide-slate-100 z-50 text-slate-700">
                            <div class="p-3.5 bg-slate-50/70 rounded-t-xl">
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Akun Terdaftar</p>
                                <p class="text-xs font-bold text-slate-900 truncate mt-0.5">{{ auth()->user()->name }}</p>
                                <p class="text-[11px] text-bps-700 font-semibold truncate">
                                    {{ auth()->user()->isAdmin() ? 'Administrator & Pembimbing BPS' : (auth()->user()->internProfile->division ?? 'Peserta Magang BPS') }}
                                </p>
                                <p class="text-[10px] text-slate-500 font-mono mt-0.5 truncate">{{ auth()->user()->email }}</p>
                            </div>
                            <div class="py-1">
                                @if(auth()->user()->isIntern())
                                <a href="{{ route('intern.profil') }}" class="flex items-center gap-2 px-4 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-bps-700 transition">
                                    <i data-lucide="user" class="w-3.5 h-3.5 text-slate-400"></i>
                                    <span>Data Profil Akademik</span>
                                </a>
                                @else
                                <a href="{{ route('admin.settings.index') }}" class="flex items-center gap-2 px-4 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-bps-700 transition">
                                    <i data-lucide="settings" class="w-3.5 h-3.5 text-slate-400"></i>
                                    <span>Pengaturan Sistem</span>
                                </a>
                                @endif
                                <button type="button" onclick="confirmLogout()" class="w-full text-left flex items-center gap-2 px-4 py-2 text-xs font-medium text-rose-600 hover:bg-rose-50 transition">
                                    <i data-lucide="log-out" class="w-3.5 h-3.5 text-rose-500"></i>
                                    <span>Keluar dari Sistem</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content Body -->
            <main class="flex-1 w-full px-3 sm:px-6 lg:px-8 py-5 sm:py-6 pb-24 lg:pb-8 max-w-7xl mx-auto">
                @yield('content')
            </main>

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
        </div>
    </div>

    <!-- Mobile Bottom Navigation Bar for Intern/Admin -->
    @if(auth()->user()->isIntern())
    <nav class="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-t border-slate-200 shadow-lg px-2 py-1 flex items-center justify-around">
        <a href="{{ route('intern.dashboard') }}" class="flex flex-col items-center gap-0.5 py-1 px-3 rounded-lg transition {{ request()->routeIs('intern.dashboard') ? 'text-bps-900 font-bold' : 'text-slate-500 hover:text-slate-800' }}">
            <i data-lucide="layout-dashboard" class="w-5 h-5 {{ request()->routeIs('intern.dashboard') ? 'stroke-[2.5]' : '' }}"></i>
            <span class="text-[10px]">Beranda</span>
        </a>

        <a href="{{ route('intern.absen') }}" class="flex flex-col items-center gap-0.5 py-1 px-3 rounded-lg transition {{ request()->routeIs('intern.absen') ? 'text-bps-900 font-bold' : 'text-slate-500 hover:text-slate-800' }}">
            <i data-lucide="clipboard-check" class="w-5 h-5 {{ request()->routeIs('intern.absen') ? 'stroke-[2.5]' : '' }}"></i>
            <span class="text-[10px]">Presensi</span>
        </a>

        <a href="{{ route('intern.riwayat') }}" class="flex flex-col items-center gap-0.5 py-1 px-3 rounded-lg transition {{ request()->routeIs('intern.riwayat') ? 'text-bps-900 font-bold' : 'text-slate-500 hover:text-slate-800' }}">
            <i data-lucide="history" class="w-5 h-5 {{ request()->routeIs('intern.riwayat') ? 'stroke-[2.5]' : '' }}"></i>
            <span class="text-[10px]">Riwayat</span>
        </a>

        <a href="{{ route('intern.profil') }}" class="flex flex-col items-center gap-0.5 py-1 px-3 rounded-lg transition {{ request()->routeIs('intern.profil') ? 'text-bps-900 font-bold' : 'text-slate-500 hover:text-slate-800' }}">
            <i data-lucide="user" class="w-5 h-5 {{ request()->routeIs('intern.profil') ? 'stroke-[2.5]' : '' }}"></i>
            <span class="text-[10px]">Profil</span>
        </a>
    </nav>
    @else
    <nav class="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-t border-slate-200 shadow-lg px-2 py-1 flex items-center justify-around">
        <a href="{{ route('admin.dashboard') }}" class="flex flex-col items-center gap-0.5 py-1 px-3 rounded-lg transition {{ request()->routeIs('admin.dashboard') ? 'text-bps-900 font-bold' : 'text-slate-500 hover:text-slate-800' }}">
            <i data-lucide="layout-dashboard" class="w-5 h-5 {{ request()->routeIs('admin.dashboard') ? 'stroke-[2.5]' : '' }}"></i>
            <span class="text-[10px]">Dashboard</span>
        </a>

        <a href="{{ route('admin.verifikasi.index') }}" class="flex flex-col items-center gap-0.5 py-1 px-3 rounded-lg relative transition {{ request()->routeIs('admin.verifikasi.*') ? 'text-bps-900 font-bold' : 'text-slate-500 hover:text-slate-800' }}">
            <i data-lucide="check-check" class="w-5 h-5 {{ request()->routeIs('admin.verifikasi.*') ? 'stroke-[2.5]' : '' }}"></i>
            <span class="text-[10px]">Verifikasi</span>
            @if($pendingTotal > 0)
            <span class="absolute top-0 right-2 w-2 h-2 rounded-full bg-amber-500 ring-2 ring-white"></span>
            @endif
        </a>

        <a href="{{ route('admin.rekap.index') }}" class="flex flex-col items-center gap-0.5 py-1 px-3 rounded-lg transition {{ request()->routeIs('admin.rekap.*') ? 'text-bps-900 font-bold' : 'text-slate-500 hover:text-slate-800' }}">
            <i data-lucide="file-spreadsheet" class="w-5 h-5 {{ request()->routeIs('admin.rekap.*') ? 'stroke-[2.5]' : '' }}"></i>
            <span class="text-[10px]">Rekap</span>
        </a>

        <button @click="sidebarOpen = !sidebarOpen" type="button" class="flex flex-col items-center gap-0.5 py-1 px-3 rounded-lg text-slate-500 hover:text-slate-800 transition">
            <i data-lucide="menu" class="w-5 h-5"></i>
            <span class="text-[10px]">Menu</span>
        </button>
    </nav>
    @endif

    <!-- Global Logout Form -->
    <form id="logoutForm" method="POST" action="{{ route('logout') }}" class="hidden">
        @csrf
    </form>
    @else
    <!-- Guest Layout (Login Page) -->
    <main class="w-full min-h-screen">
        @yield('content')
    </main>
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
            confirmColor = '#002855',
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

    @if($errors->any())
    <script>
        (function() {
            function showFormErrors() {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Data Belum Lengkap / Tidak Sesuai Syarat',
                        html: '<div class="text-left text-xs space-y-1"><ul class="list-disc list-inside">{!! implode("", array_map(fn($e) => "<li>" . e($e) . "</li>", $errors->all())) !!}</ul></div>',
                        confirmButtonColor: '#002855',
                        confirmButtonText: 'Tutup & Lengkapi'
                    });
                }
            }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', showFormErrors);
            } else {
                showFormErrors();
            }
        })();
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