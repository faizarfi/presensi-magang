@extends('layouts.app')

@section('title', 'Profil Akademik Peserta Magang - SIM-MAGANG BPS')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Title & Breadcrumb -->
    <div>
        <div class="flex items-center space-x-2 text-xs text-slate-500 mb-1">
            <a href="{{ route('intern.dashboard') }}" class="hover:text-blue-800 flex items-center gap-1">
                <i data-lucide="home" class="w-3.5 h-3.5"></i>
                <span>Beranda</span>
            </a>
            <span>/</span>
            <span class="text-slate-800 font-semibold">Profil Peserta</span>
        </div>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Profil Peserta Magang BPS</h1>
        <p class="text-xs text-slate-500 mt-0.5">Kelola informasi kontak, data domisili, dan keamanan kata sandi akun magang Anda</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Left: Summary Info Card (Readonly) -->
        <div class="bg-white rounded-xl p-5 sm:p-6 shadow-xs border border-slate-200 text-center space-y-4">
            <div class="w-20 h-20 rounded-full mx-auto bg-blue-50 border border-blue-200 flex items-center justify-center text-blue-900 text-2xl font-bold overflow-hidden">
                @if($user->avatar)
                    <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                @else
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                @endif
            </div>

            <div>
                <h2 class="text-base font-bold text-slate-900">{{ $user->name }}</h2>
                <p class="text-xs text-slate-500 font-mono mt-0.5">{{ $user->email }}</p>
                <div class="mt-2 inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-800 border border-blue-200">
                    <i data-lucide="building-2" class="w-3.5 h-3.5 text-blue-700"></i>
                    <span>Seksi {{ $user->internProfile->division ?? 'Subbagian Umum' }}</span>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 text-left text-xs space-y-2.5">
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-semibold">NIM / NISN</span>
                    <span class="font-bold text-slate-800 font-mono text-xs">{{ $user->internProfile->nim_nisn ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-semibold">Asal Universitas / Sekolah</span>
                    <span class="font-medium text-slate-800">{{ $user->internProfile->institution ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-semibold">Program Studi / Jurusan</span>
                    <span class="font-medium text-slate-800">{{ $user->internProfile->major ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-semibold">Pembimbing Lapangan BPS</span>
                    <span class="font-semibold text-blue-900">{{ $user->internProfile->mentor_name ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-semibold">Durasi & Periode Magang</span>
                    <span class="font-bold text-slate-800 text-xs block">
                        {{ $user->internProfile->duration_label ?? '3 Bulan' }}
                    </span>
                    <span class="font-medium text-slate-600 text-[11px]">
                        {{ $user->internProfile->start_date ? $user->internProfile->start_date->translatedFormat('d M Y') : '-' }}
                        s/d
                        {{ $user->internProfile->end_date ? $user->internProfile->end_date->translatedFormat('d M Y') : '-' }}
                    </span>
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100">
                <span class="text-[11px] text-slate-400 block italic leading-snug">
                    * Perubahan data akademik dan divisi penempatan diatur langsung oleh Admin BPS Karanganyar.
                </span>
            </div>
        </div>

        <!-- Right: Edit Form (2 cols) -->
        <div class="md:col-span-2 bg-white rounded-xl p-5 sm:p-6 shadow-xs border border-slate-200">
            <h2 class="text-base font-bold text-slate-900 mb-0.5">Perbarui Kontak & Keamanan Akun</h2>
            <p class="text-xs text-slate-500 mb-5">Pastikan nomor telepon WhatsApp Anda aktif agar pembimbing BPS mudah menghubungi</p>

            <form id="profileUpdateForm" action="{{ route('intern.profil.update') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                               class="w-full h-10 text-xs sm:text-sm px-3 rounded-lg border border-slate-300 focus:ring-1 focus:ring-blue-800 outline-none font-medium">
                    </div>

                    <div>
                        <label for="phone" class="block text-xs font-semibold text-slate-700 mb-1">Nomor HP / WhatsApp</label>
                        <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}" placeholder="08xxxxxxxxxx"
                               class="w-full h-10 text-xs sm:text-sm px-3 rounded-lg border border-slate-300 focus:ring-1 focus:ring-blue-800 outline-none font-mono">
                    </div>
                </div>

                <div>
                    <label for="avatar" class="block text-xs font-semibold text-slate-700 mb-1">Foto Profil Baru (Opsional)</label>
                    <input type="file" name="avatar" id="avatar" accept="image/*"
                           class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-800 hover:file:bg-blue-100 border border-slate-300 rounded-lg">
                    <p class="text-[11px] text-slate-400 mt-1">Format gambar: JPG, JPEG, atau PNG. Maksimal 2 MB.</p>
                </div>

                <div>
                    <label for="address" class="block text-xs font-semibold text-slate-700 mb-1">Alamat Domisili Selama Magang</label>
                    <textarea name="address" id="address" rows="2" placeholder="Alamat rumah / tempat tinggal sementara di Karanganyar..."
                              class="w-full text-xs sm:text-sm p-3 rounded-lg border border-slate-300 focus:ring-1 focus:ring-blue-800 outline-none leading-relaxed">{{ old('address', $user->internProfile->address ?? '') }}</textarea>
                </div>

                <div>
                    <label for="bio" class="block text-xs font-semibold text-slate-700 mb-1">Minat Riset / Keahlian</label>
                    <textarea name="bio" id="bio" rows="2" placeholder="Minat keahlian Anda (misal: analisis data spasial, visualisasi statistik, web development)..."
                              class="w-full text-xs sm:text-sm p-3 rounded-lg border border-slate-300 focus:ring-1 focus:ring-blue-800 outline-none leading-relaxed">{{ old('bio', $user->internProfile->bio ?? '') }}</textarea>
                </div>

                <!-- Ganti Password Section -->
                <div class="pt-5 border-t border-slate-200 space-y-3" x-data="{ showCurrent: false, showNew: false, showConfirm: false }">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                            <i data-lucide="shield-check" class="w-4 h-4 text-blue-800"></i>
                            <span>Keamanan Akun: Ubah Kata Sandi</span>
                        </h3>
                        <span class="text-[11px] text-slate-400">Kosongkan jika tidak ingin mengubah</span>
                    </div>

                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-3">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <!-- Kata Sandi Saat Ini -->
                            <div>
                                <label for="current_password" class="block text-xs font-semibold text-slate-700 mb-1">
                                    Kata Sandi Saat Ini
                                </label>
                                <div class="relative">
                                    <input :type="showCurrent ? 'text' : 'password'" name="current_password" id="current_password" placeholder="••••••••"
                                           class="w-full h-10 text-xs sm:text-sm px-3 pr-10 rounded-lg border {{ $errors->has('current_password') ? 'border-rose-400 ring-1 ring-rose-300' : 'border-slate-300' }} focus:ring-1 focus:ring-blue-800 outline-none font-medium bg-white">
                                    <button type="button" @click="showCurrent = !showCurrent" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 transition">
                                        <i :data-lucide="showCurrent ? 'eye-off' : 'eye'" class="w-4 h-4"></i>
                                    </button>
                                </div>
                                @error('current_password')
                                    <span class="text-[11px] text-rose-600 font-medium block mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Kata Sandi Baru -->
                            <div>
                                <label for="new_password" class="block text-xs font-semibold text-slate-700 mb-1">
                                    Kata Sandi Baru
                                </label>
                                <div class="relative">
                                    <input :type="showNew ? 'text' : 'password'" name="new_password" id="new_password" placeholder="••••••••"
                                           class="w-full h-10 text-xs sm:text-sm px-3 pr-10 rounded-lg border {{ $errors->has('new_password') ? 'border-rose-400 ring-1 ring-rose-300' : 'border-slate-300' }} focus:ring-1 focus:ring-blue-800 outline-none font-medium bg-white">
                                    <button type="button" @click="showNew = !showNew" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 transition">
                                        <i :data-lucide="showNew ? 'eye-off' : 'eye'" class="w-4 h-4"></i>
                                    </button>
                                </div>
                                @error('new_password')
                                    <span class="text-[11px] text-rose-600 font-medium block mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Konfirmasi Kata Sandi Baru -->
                            <div class="sm:col-span-2">
                                <label for="new_password_confirmation" class="block text-xs font-semibold text-slate-700 mb-1">
                                    Ulangi Konfirmasi Kata Sandi Baru
                                </label>
                                <div class="relative">
                                    <input :type="showConfirm ? 'text' : 'password'" name="new_password_confirmation" id="new_password_confirmation" placeholder="••••••••"
                                           class="w-full h-10 text-xs sm:text-sm px-3 pr-10 rounded-lg border border-slate-300 focus:ring-1 focus:ring-blue-800 outline-none font-medium bg-white">
                                    <button type="button" @click="showConfirm = !showConfirm" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 transition">
                                        <i :data-lucide="showConfirm ? 'eye-off' : 'eye'" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="text-[11px] text-slate-500 flex items-center gap-1.5 pt-1">
                            <i data-lucide="info" class="w-3.5 h-3.5 text-blue-800 flex-shrink-0"></i>
                            <span>Kata sandi minimal 8 karakter. Disarankan kombinasi huruf kapital, huruf kecil, dan angka.</span>
                        </div>
                    </div>
                </div>

                <div class="pt-3 flex justify-end">
                    <button type="submit" class="h-10 px-5 rounded-lg bg-blue-800 hover:bg-blue-900 text-white text-xs font-semibold transition shadow-xs flex items-center gap-2">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        <span>Simpan Perubahan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
