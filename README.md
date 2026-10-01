# SIM-MAGANG - BPS Kabupaten Karanganyar

Sistem Informasi Manajemen Presensi & Evaluasi Magang berbasis Web pada **Badan Pusat Statistik (BPS) Kabupaten Karanganyar**.

Sistem ini dirancang dengan standar keamanan tinggi dan fitur anti-kecurangan untuk mengelola kehadiran, logbook harian, serta evaluasi akhir mahasiswa/siswa magang secara akurat, tertib, dan transparan.

---

## 🌟 Fitur Utama

### 1. Keamanan & Anti-Kecurangan (Anti-Fraud)
- **Device Binding (Anti Titip Absen)**: Akun peserta magang terkunci otomatis ke 1 perangkat (HP/Laptop) berdasarkan *device fingerprint*. Login dari perangkat berbeda ditolak otomatis dan hanya dapat direset oleh Admin BPS.
- **Geofencing GPS Radius Kantor**: Presensi hanya dapat dilakukan jika koordinat GPS peserta berada dalam radius kantor BPS Karanganyar (validasi rumus *Haversine* di sisi backend).
- **Mandatori Kamera Selfie**: Wajib mengambil swafoto langsung saat presensi masuk dan pulang.
- **Private Document Storage**: Berkas sensitif (surat izin, surat sakit dokter) disimpan di *storage private* dengan proteksi otorisasi ketat (hanya dapat diakses Admin atau peserta yang bersangkutan).

### 2. Presensi Masuk, Pulang & Deteksi Keterlambatan
- **Presensi Pulang (Check-Out)**: Mencatat jam kepulangan, verifikasi radius kantor, swafoto pulang, serta menghitung durasi jam kerja efektif per hari.
- **Deteksi Keterlambatan Otomatis**: Membandingkan jam check-in dengan batas jam kerja BPS (default: `07:30`). Menghitung selisih menit keterlambatan dan mewajibkan pengisian alasan keterlambatan.

### 3. Penilaian Akhir & Form Evaluasi Magang
- **5 Rubrik Penilaian Formal**:
  1. Disiplin & Ketepatan Waktu (25%)
  2. Tanggung Jawab & Integritas (25%)
  3. Inisiatif & Keaktifan (15%)
  4. Kerjasama Tim & Komunikasi (15%)
  5. Kualitas Hasil Pekerjaan (20%)
- **Kalkulasi Otomatis**: Nilai akhir (skala 100) dan predikat (A: Sangat Baik, B: Baik, C: Cukup, D: Kurang, E: Sangat Kurang) dihitung otomatis secara *real-time*.
- **Cetak Lembar Nilai Resmi**: Format cetak standar BPS Karanganyar lengkap dengan Kop Surat, rincian skor, catatan pembimbing, dan kolom tanda tangan pembimbing lapangan.

### 4. Manajemen Admin & Rekapitulasi
- **Verifikasi Kehadiran**: Persetujuan (ACC) presensi harian, komparasi foto masuk & pulang, serta fitur *Bulk Approve*.
- **Manajemen Peserta**: Pendaftaran mahasiswa magang, penentuan periode dan durasi magang, serta fitur *Reset Kunci HP*.
- **Rekap & Ekspor Laporan**: Ekspor rekapitulasi kehadiran periodik dalam format PDF matriks horizontal dan CSV/Excel.
- **Kalender Hari Libur**: Pengelolaan hari libur nasional dan cuti bersama otomatis menonaktifkan sanksi absensi.

---

## 🛠️ Tech Stack

- **Backend Framework**: [Laravel 12](https://laravel.com) (PHP 8.2+)
- **Database**: MySQL / MariaDB (Mendukung SQLite untuk pengujian)
- **Frontend & Styling**: Tailwind CSS, Alpine.js, Lucide Icons, Leaflet Maps
- **Komponen Pendukung**: SweetAlert2, HTML5 Geolocation API, MediaDevices WebRTC API
- **Testing**: PHPUnit / Pest PHP (15 Test Suites, 109 Assertions)

---

## 🚀 Panduan Instalasi

### 1. Kloning Repositori
```bash
git clone https://github.com/faizarfi/presensi-magang.git
cd presensi-magang
```

### 2. Pasang Dependensi
```bash
composer install
npm install
```

### 3. Konfigurasi Environment
Salin berkas `.env.example` menjadi `.env` dan sesuaikan kredensial database Anda:
```bash
cp .env.example .env
php artisan key:generate
```

Sesuaikan konfigurasi database pada `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=absensi
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Migrasi & Seeder Database
```bash
php artisan migrate --seed
php artisan storage:link
```

### 5. Kompilasi Aset Frontend
```bash
npm run build
```

### 6. Menjalankan Server Lokal
```bash
composer run dev
# atau
php artisan serve
```
Akses aplikasi melalui peramban di: **`http://localhost:8000`**

---

## 🧪 Menjalankan Pengujian (Automated Tests)

Semua fungsionalitas dan logika keamanan telah dilengkapi pengujian otomatis:
```bash
php artisan test
```

---

## 📄 Lisensi
Sistem ini dikembangkan untuk keperluan operasional Badan Pusat Statistik (BPS) Kabupaten Karanganyar. Hak cipta dilindungi.
