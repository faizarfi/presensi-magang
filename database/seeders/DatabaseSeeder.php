<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\InternProfile;
use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Settings Kantor BPS Karanganyar
        $settings = [
            'office_name' => ['value' => 'BPS Kabupaten Karanganyar', 'description' => 'Nama Instansi'],
            'office_address' => ['value' => 'Komplek Perkantoran Cangakan, Jl. Majapahit No. 11 B, Badran Asri, Bejen, Karanganyar 57712', 'description' => 'Alamat Kantor'],
            'office_latitude' => ['value' => '-7.5978000', 'description' => 'Latitude Titik Kantor BPS'],
            'office_longitude' => ['value' => '110.9505000', 'description' => 'Longitude Titik Kantor BPS'],
            'office_radius_meters' => ['value' => '150', 'description' => 'Toleransi Radius Kehadiran (meter)'],
            'work_start_time' => ['value' => '07:30', 'description' => 'Jam Mulai Kerja'],
            'work_end_time' => ['value' => '16:00', 'description' => 'Jam Selesai Kerja'],
        ];

        foreach ($settings as $key => $data) {
            Setting::updateOrCreate(['key' => $key], $data);
        }

        // 2. Daftar Tanggal Merah Indonesia (Tahun 2026 & 2025)
        $holidays = [
            ['holiday_date' => '2026-01-01', 'name' => 'Tahun Baru 2026 Masehi'],
            ['holiday_date' => '2026-01-16', 'name' => 'Isra Mi\'raj Nabi Muhammad SAW'],
            ['holiday_date' => '2026-02-17', 'name' => 'Tahun Baru Imlek 2577'],
            ['holiday_date' => '2026-03-20', 'name' => 'Hari Suci Nyepi Tahun Baru Saka 1948'],
            ['holiday_date' => '2026-03-21', 'name' => 'Hari Raya Idul Fitri 1447 H (Hari 1)'],
            ['holiday_date' => '2026-03-22', 'name' => 'Hari Raya Idul Fitri 1447 H (Hari 2)'],
            ['holiday_date' => '2026-04-03', 'name' => 'Wafat Isa Almasih (Jumat Agung)'],
            ['holiday_date' => '2026-05-01', 'name' => 'Hari Buruh Internasional'],
            ['holiday_date' => '2026-05-14', 'name' => 'Kenaikan Isa Almasih'],
            ['holiday_date' => '2026-05-31', 'name' => 'Hari Raya Waisak 2570'],
            ['holiday_date' => '2026-06-01', 'name' => 'Hari Lahir Pancasila'],
            ['holiday_date' => '2026-05-27', 'name' => 'Hari Raya Idul Adha 1447 H'],
            ['holiday_date' => '2026-06-16', 'name' => 'Tahun Baru Islam 1448 Hijriah'],
            ['holiday_date' => '2026-08-17', 'name' => 'Hari Kemerdekaan Republik Indonesia ke-81'],
            ['holiday_date' => '2026-08-25', 'name' => 'Maulid Nabi Muhammad SAW'],
            ['holiday_date' => '2026-12-25', 'name' => 'Hari Raya Natal'],
        ];

        foreach ($holidays as $h) {
            Holiday::updateOrCreate(['holiday_date' => $h['holiday_date']], $h);
        }

        // 3. User Admin / Pembimbing BPS
        $admin = User::updateOrCreate(
            ['email' => 'admin@bps.go.id'],
            [
                'name' => 'Admin Pembimbing BPS Karanganyar',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'phone' => '089605933133',
            ]
        );

        // Alias admin@example.com untuk kemudahan pengujian
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin Pembimbing BPS (Demo)',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'phone' => '089605933133',
            ]
        );

        // 4. User Anak Magang 1: Budi Prasetyo
        $intern1 = User::updateOrCreate(
            ['email' => 'magang@bps.go.id'],
            [
                'name' => 'Budi Prasetyo',
                'password' => Hash::make('password'),
                'role' => 'intern',
                'phone' => '081234567890',
            ]
        );

        InternProfile::updateOrCreate(
            ['user_id' => $intern1->id],
            [
                'nim_nisn' => '21010120140011',
                'institution' => 'Universitas Sebelas Maret (UNS)',
                'major' => 'Informatika / Statistika',
                'division' => 'Integrasi Pengolahan & Diseminasi Statistik (IPDS)',
                'mentor_name' => 'Drs. Suryanto, M.Si',
                'duration_months' => 3,
                'start_date' => Carbon::now()->subDays(30)->toDateString(),
                'end_date' => Carbon::now()->addDays(60)->toDateString(),
                'address' => 'Jl. Lawu No. 45, Karanganyar, Jawa Tengah',
                'bio' => 'Mahasiswa tingkat akhir yang tertarik pada pengolahan data statistik, visualisasi data spasial, dan digitalisasi layanan BPS.',
            ]
        );

        // User Anak Magang 2: Siti Rahmawati
        $intern2 = User::updateOrCreate(
            ['email' => 'siti@bps.go.id'],
            [
                'name' => 'Siti Rahmawati',
                'password' => Hash::make('password'),
                'role' => 'intern',
                'phone' => '081234567891',
            ]
        );

        InternProfile::updateOrCreate(
            ['user_id' => $intern2->id],
            [
                'nim_nisn' => '21010120140022',
                'institution' => 'Universitas Diponegoro (UNDIP)',
                'major' => 'Statistika',
                'division' => 'Statistik Sosial',
                'mentor_name' => 'Drs. Suryanto, M.Si',
                'duration_months' => 3,
                'start_date' => Carbon::now()->subDays(30)->toDateString(),
                'end_date' => Carbon::now()->addDays(60)->toDateString(),
                'address' => 'Jl. Tentara Pelajar No. 12, Karanganyar',
                'bio' => 'Mahasiswi Statistika dengan fokus pengolahan data survei sosial ekonomi dan sensus penduduk.',
            ]
        );

        // User Anak Magang 3: Dimas Pratama
        $intern3 = User::updateOrCreate(
            ['email' => 'dimas@bps.go.id'],
            [
                'name' => 'Dimas Pratama',
                'password' => Hash::make('password'),
                'role' => 'intern',
                'phone' => '081234567892',
            ]
        );

        InternProfile::updateOrCreate(
            ['user_id' => $intern3->id],
            [
                'nim_nisn' => '21010120140033',
                'institution' => 'Universitas Dian Nuswantoro (UDINUS)',
                'major' => 'Sistem Informasi',
                'division' => 'Statistik Produksi',
                'mentor_name' => 'Drs. Suryanto, M.Si',
                'duration_months' => 4,
                'start_date' => Carbon::now()->subDays(30)->toDateString(),
                'end_date' => Carbon::now()->addDays(90)->toDateString(),
                'address' => 'Jl. Tasikmadu No. 8, Karanganyar',
                'bio' => 'Mahasiswa Sistem Informasi tertarik otomasi data statistik pertanian dan industri manufaktur.',
            ]
        );

        // User Anak Magang 4: Putri Ayu Wardani
        $intern4 = User::updateOrCreate(
            ['email' => 'putri@bps.go.id'],
            [
                'name' => 'Putri Ayu Wardani',
                'password' => Hash::make('password'),
                'role' => 'intern',
                'phone' => '081234567893',
            ]
        );

        InternProfile::updateOrCreate(
            ['user_id' => $intern4->id],
            [
                'nim_nisn' => '21010120140044',
                'institution' => 'Universitas Sebelas Maret (UNS)',
                'major' => 'Administrasi Negara',
                'division' => 'Subbagian Umum',
                'mentor_name' => 'Drs. Suryanto, M.Si',
                'duration_months' => 3,
                'start_date' => Carbon::now()->subDays(30)->toDateString(),
                'end_date' => Carbon::now()->addDays(60)->toDateString(),
                'address' => 'Jl. Jaten No. 19, Karanganyar',
                'bio' => 'Mahasiswi Administrasi Negara mendalami tata kelola persuratan dan kepegawaian BPS.',
            ]
        );

        // User Anak Magang 5: Ahmad Fauzi
        $intern5 = User::updateOrCreate(
            ['email' => 'fauzi@bps.go.id'],
            [
                'name' => 'Ahmad Fauzi',
                'password' => Hash::make('password'),
                'role' => 'intern',
                'phone' => '081234567894',
            ]
        );

        InternProfile::updateOrCreate(
            ['user_id' => $intern5->id],
            [
                'nim_nisn' => '21010120140055',
                'institution' => 'Universitas Gadjah Mada (UGM)',
                'major' => 'Ilmu Ekonomi',
                'division' => 'Neraca Wilayah & Analisis Statistik',
                'mentor_name' => 'Drs. Suryanto, M.Si',
                'duration_months' => 6,
                'start_date' => Carbon::now()->subDays(30)->toDateString(),
                'end_date' => Carbon::now()->addDays(150)->toDateString(),
                'address' => 'Jl. Solo-Tawangmangu Km. 7, Karanganyar',
                'bio' => 'Mahasiswa Ilmu Ekonomi yang fokus pada penyusunan PDRB dan analisis tabel input-output.',
            ]
        );

        // 5. Seed sample attendances untuk seluruh anak magang di bulan berjalan
        $allInterns = [$intern1, $intern2, $intern3, $intern4, $intern5];
        $workdaysPast = [];
        for ($i = 1; $i <= 8; $i++) {
            $dt = Carbon::now()->subDays($i);
            $dayCheck = Holiday::checkDayStatus($dt);
            if (!$dayCheck['is_off']) {
                $workdaysPast[] = $dt;
            }
        }

        foreach ($allInterns as $idx => $internUser) {
            foreach ($workdaysPast as $wIndex => $workDate) {
                // Beragam pola kehadiran realistis
                $isHadir = true;
                $status = 'hadir';
                $subStatus = 'hadir_kantor';
                $time = '07:' . sprintf('%02d', 20 + ($idx * 3) + $wIndex) . ':15';
                $appStatus = 'approved';

                if ($idx === 1 && $wIndex === 2) {
                    // Siti izin 1 hari
                    $status = 'tidak_hadir_keterangan';
                    $subStatus = 'izin';
                    $time = null;
                } elseif ($idx === 4 && $wIndex === 1) {
                    // Fauzi sakit 1 hari
                    $status = 'tidak_hadir_keterangan';
                    $subStatus = 'sakit';
                    $time = null;
                }

                Attendance::updateOrCreate(
                    [
                        'user_id' => $internUser->id,
                        'date' => $workDate->toDateString(),
                    ],
                    [
                        'status' => $status,
                        'sub_status' => $subStatus,
                        'check_in_time' => $time,
                        'latitude' => $time ? -7.597810 : null,
                        'longitude' => $time ? 110.950510 : null,
                        'distance_meters' => $time ? (15.0 + $idx * 4) : null,
                        'location_address' => 'Komplek Perkantoran Cangakan, BPS Kab. Karanganyar',
                        'activity_description' => 'Tugas kedinasan seksi ' . ($internUser->internProfile->division ?? 'BPS'),
                        'learning_obtained' => 'Pemahaman alur kerja dan pengolahan data BPS Karanganyar.',
                        'constraints_encountered' => '-',
                        'is_confirmed' => true,
                        'approval_status' => $appStatus,
                        'approved_by' => $admin->id,
                        'approved_at' => $workDate->copy()->addHours(9),
                        'approval_notes' => 'Disetujui pembimbing.',
                    ]
                );
            }
        }
    }
}
