<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\InternProfile;
use App\Models\Setting;
use App\Models\User;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AttendanceSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * Test 1: Hitung Jarak Haversine dan Validasi Radius Kantor BPS Karanganyar
     */
    public function test_haversine_distance_and_office_radius(): void
    {
        // Kantor BPS Karanganyar: Lat -7.5978, Lng 110.9505, Radius 150m
        // 1. Titik di dalam kantor (jarak ~10-20m)
        $insideCheck = AttendanceService::checkOfficeRadius(-7.597810, 110.950510);
        $this->assertTrue($insideCheck['is_inside']);
        $this->assertLessThanOrEqual(150, $insideCheck['distance_meters']);

        // 2. Titik di luar kantor (Solo / ~14 km dari Karanganyar)
        $outsideCheck = AttendanceService::checkOfficeRadius(-7.5561, 110.8316);
        $this->assertFalse($outsideCheck['is_inside']);
        $this->assertGreaterThan(1000, $outsideCheck['distance_meters']);
    }

    /**
     * Test 2: Deteksi Hari Libur (Sabtu, Minggu, dan Tanggal Merah)
     */
    public function test_holiday_and_weekend_detection(): void
    {
        // Sabtu (2026-09-12 adalah Sabtu)
        $saturday = Carbon::parse('2026-09-12');
        $satStatus = Holiday::checkDayStatus($saturday);
        $this->assertTrue($satStatus['is_off']);
        $this->assertEquals('weekend', $satStatus['type']);

        // Minggu (2026-09-13 adalah Minggu)
        $sunday = Carbon::parse('2026-09-13');
        $sunStatus = Holiday::checkDayStatus($sunday);
        $this->assertTrue($sunStatus['is_off']);
        $this->assertEquals('weekend', $sunStatus['type']);

        // Tanggal Merah 17 Agustus 2026 (Hari Kemerdekaan RI)
        $riDay = Carbon::parse('2026-08-17');
        $riStatus = Holiday::checkDayStatus($riDay);
        $this->assertTrue($riStatus['is_off']);
        $this->assertEquals('national_holiday', $riStatus['type']);

        // Hari Kerja Biasa
        $workday = Carbon::parse('2026-09-09'); // Rabu
        $workStatus = Holiday::checkDayStatus($workday);
        $this->assertFalse($workStatus['is_off']);
        $this->assertEquals('workday', $workStatus['type']);
    }

    /**
     * Test 3: Evaluasi Otomatis Hari Kosong Lewat Jam 12 Malam Menjadi Alpa
     */
    public function test_automatic_absence_evaluation(): void
    {
        $intern = User::where('email', 'magang@bps.go.id')->first();

        // Buat tanggal mulai magang 3 hari yang lalu
        $threeDaysAgo = Carbon::today()->subDays(3);
        $intern->internProfile->update([
            'start_date' => $threeDaysAgo->toDateString(),
            'end_date' => Carbon::today()->addDays(30)->toDateString(),
        ]);

        // Hapus absensi sebelumnya untuk pengujian bersih
        Attendance::where('user_id', $intern->id)->delete();

        // Jalankan evaluasi
        $evaluatedCount = AttendanceService::evaluateAbsenceForIntern($intern);

        $this->assertGreaterThanOrEqual(1, $evaluatedCount);

        // Pastikan record yang dibuat bertipe alpa otomatis
        $alpaRecord = Attendance::where('user_id', $intern->id)
            ->where('status', 'tidak_hadir_tanpa_keterangan')
            ->first();

        $this->assertNotNull($alpaRecord);
        $this->assertEquals('alpa_otomatis', $alpaRecord->sub_status);
    }

    /**
     * Test 4: Autorisasi Hak Akses Role
     */
    public function test_role_based_access_control(): void
    {
        $admin = User::where('email', 'admin@bps.go.id')->first();
        $intern = User::where('email', 'magang@bps.go.id')->first();

        // Test Login POST untuk admin dan intern
        $adminLogin = $this->post(route('login.post'), [
            'email' => 'admin@bps.go.id',
            'password' => 'password',
        ]);
        $adminLogin->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);

        $this->post(route('logout'));

        $internLogin = $this->post(route('login.post'), [
            'email' => 'magang@bps.go.id',
            'password' => 'password',
        ]);
        $internLogin->assertRedirect(route('intern.dashboard'));
        $this->assertAuthenticatedAs($intern);

        // Intern mencoba akses admin panel -> harus dialihkan ke intern dashboard
        $response = $this->actingAs($intern)->get(route('admin.dashboard'));
        $response->assertRedirect(route('intern.dashboard'));

        // Admin mengakses admin dashboard -> 200 OK
        $adminResponse = $this->actingAs($admin)->get(route('admin.dashboard'));
        $adminResponse->assertStatus(200);

        // Intern mengakses intern dashboard -> 200 OK
        $internResponse = $this->actingAs($intern)->get(route('intern.dashboard'));
        $internResponse->assertStatus(200);
    }

    /**
     * Test 5: Verifikasi Tunggal & Bulk ACC oleh Admin
     */
    public function test_admin_single_and_bulk_acc(): void
    {
        $admin = User::where('email', 'admin@bps.go.id')->first();
        $intern = User::where('email', 'magang@bps.go.id')->first();

        // Buat presensi pending
        $attendance = Attendance::create([
            'user_id' => $intern->id,
            'date' => Carbon::today()->toDateString(),
            'status' => 'hadir',
            'sub_status' => 'hadir_kantor',
            'check_in_time' => '07:30:00',
            'latitude' => -7.5978,
            'longitude' => 110.9505,
            'distance_meters' => 12.0,
            'activity_description' => 'Mengerjakan kompilasi data statistik daerah.',
            'learning_obtained' => 'Validasi kuesioner Sakernas.',
            'constraints_encountered' => '-',
            'is_confirmed' => true,
            'approval_status' => 'pending',
        ]);

        // 1. Single ACC
        $response = $this->actingAs($admin)->put(route('admin.verifikasi.update', $attendance->id), [
            'approval_status' => 'approved',
            'approval_notes' => 'Laporan disetujui.',
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals('approved', $attendance->fresh()->approval_status);
        $this->assertEquals($admin->id, $attendance->fresh()->approved_by);

        // 2. Bulk ACC
        $attendance2 = Attendance::updateOrCreate(
            ['user_id' => $intern->id, 'date' => Carbon::yesterday()->toDateString()],
            [
                'status' => 'hadir',
                'activity_description' => 'Kegiatan pengolahan data susenas.',
                'approval_status' => 'pending',
            ]
        );

        $bulkResponse = $this->actingAs($admin)->post(route('admin.verifikasi.bulk'), [
            'attendance_ids' => [$attendance2->id],
        ]);

        $bulkResponse->assertSessionHas('success');
        $this->assertEquals('approved', $attendance2->fresh()->approval_status);
    }

    /**
     * Test 6: Seluruh Tampilan Blade Merender 200 OK Tanpa Kesalahan Sintaks
     */
    public function test_all_redesigned_views_render_successfully(): void
    {
        $admin = User::where('email', 'admin@bps.go.id')->first();
        $intern = User::where('email', 'magang@bps.go.id')->first();

        // 1. Guest Routes
        $this->get('/login')->assertStatus(200);

        // 2. Admin Routes
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertStatus(200);
        $this->actingAs($admin)->get(route('admin.verifikasi.index'))->assertStatus(200);
        $this->actingAs($admin)->get(route('admin.rekap.index'))->assertStatus(200);
        $this->actingAs($admin)->get(route('admin.interns.index'))->assertStatus(200);
        $this->actingAs($admin)->get(route('admin.interns.create'))->assertStatus(200);
        $this->actingAs($admin)->get(route('admin.interns.edit', $intern->id))->assertStatus(200);
        $this->actingAs($admin)->get(route('admin.interns.show', $intern->id))->assertStatus(200);
        $this->actingAs($admin)->get(route('admin.holidays.index'))->assertStatus(200);
        $this->actingAs($admin)->get(route('admin.settings.index'))->assertStatus(200);
        $this->actingAs($admin)->get(route('admin.rekap.pdf'))->assertStatus(200);

        // 3. Intern Routes
        $this->actingAs($intern)->get(route('intern.dashboard'))->assertStatus(200);
        $this->actingAs($intern)->get(route('intern.absen'))->assertStatus(200);
        $this->actingAs($intern)->get(route('intern.riwayat'))->assertStatus(200);
        $this->actingAs($intern)->get(route('intern.profil'))->assertStatus(200);
    }

    /**
     * Test 7: Pembuatan dan Perbaruan Peserta Magang dengan Durasi Bulan (1 s/d 6 Bulan)
     */
    public function test_intern_creation_and_edit_with_duration_months(): void
    {
        $admin = User::where('email', 'admin@bps.go.id')->first();

        // Buat peserta magang baru dengan durasi 6 bulan
        $response = $this->actingAs($admin)->post(route('admin.interns.store'), [
            'name' => 'Fajar Santoso',
            'email' => 'fajar@bps.go.id',
            'password' => 'password123',
            'phone' => '081299887766',
            'nim_nisn' => '23010199001',
            'institution' => 'Universitas Indonesia',
            'major' => 'Ilmu Komputer',
            'division' => 'Integrasi Pengolahan & Diseminasi Statistik (IPDS)',
            'mentor_name' => 'Drs. Suryanto, M.Si',
            'duration_months' => 6,
            'start_date' => '2026-09-01',
            'end_date' => '2027-02-28',
            'address' => 'Jl. Lawu No. 10',
        ]);

        $response->assertRedirect(route('admin.interns.index'));

        $newIntern = User::where('email', 'fajar@bps.go.id')->first();
        $this->assertNotNull($newIntern);
        $this->assertEquals(6, $newIntern->internProfile->duration_months);
        $this->assertEquals('6 Bulan', $newIntern->internProfile->duration_label);

        // Edit menjadi durasi 2 bulan
        $updateResponse = $this->actingAs($admin)->put(route('admin.interns.update', $newIntern->id), [
            'name' => 'Fajar Santoso Update',
            'email' => 'fajar@bps.go.id',
            'phone' => '081299887766',
            'institution' => 'Universitas Indonesia',
            'major' => 'Ilmu Komputer',
            'division' => 'Statistik Produksi',
            'duration_months' => 2,
            'start_date' => '2026-09-01',
            'end_date' => '2026-10-31',
        ]);

        $updateResponse->assertRedirect(route('admin.interns.index'));
        $newIntern->refresh();
        $this->assertEquals(2, $newIntern->internProfile->duration_months);
        $this->assertEquals('2 Bulan', $newIntern->internProfile->duration_label);
    }

    /**
     * Test 8: Rekap PDF Menampilkan Seluruh Anak Magang Secara Menyamping Tanpa Jurnal Logbook Harian
     */
    public function test_rekap_pdf_contains_all_interns_and_horizontal_matrix_without_daily_logbook(): void
    {
        $admin = User::where('email', 'admin@bps.go.id')->first();

        $response = $this->actingAs($admin)->get(route('admin.rekap.pdf'));

        $response->assertStatus(200);

        // Memuat seluruh peserta magang (bukan hanya Budi Prasetyo)
        $response->assertSee('Budi Prasetyo');
        $response->assertSee('Siti Rahmawati');
        $response->assertSee('Dimas Pratama');
        $response->assertSee('Putri Ayu Wardani');
        $response->assertSee('Ahmad Fauzi');

        // Memuat format menyamping dan label ACC
        $response->assertSee('DAFTAR REKAPITULASI PRESENSI BULANAN PESERTA MAGANG');
        $response->assertSee('Tanggal Presensi Bulan');
        $response->assertSee('Status ACC');
        $response->assertSee('Setujui');

        // Memastikan Jurnal Logbook Harian sudah TIDAK ditampilkan
        $response->assertDontSee('Rincian Jurnal Logbook Harian');
        $response->assertDontSee('Uraian Aktivitas yang Dikerjakan');
    }

    /**
     * Test 9: Peserta Magang Mengunggah Berkas Bukti dan Tersimpan Aman di Storage Privat (Akses Aman)
     */
    public function test_intern_can_upload_medical_and_dispensation_attachment_file(): void
    {
        Storage::fake('local');

        $intern = User::where('email', 'magang@bps.go.id')->first();
        // Bersihkan presensi hari ini jika ada
        Attendance::where('user_id', $intern->id)->where('date', Carbon::today()->toDateString())->delete();

        $file = UploadedFile::fake()->create('surat_keterangan_sakit.pdf', 800, 'application/pdf');

        $response = $this->actingAs($intern)->post(route('intern.absen.store'), [
            'status' => 'tidak_hadir_keterangan',
            'sub_status' => 'sakit',
            'activity_description' => 'Izin tidak hadir karena demam dan berobat ke faskes.',
            'attachment_file' => $file,
            'is_confirmed' => '1',
        ]);

        $response->assertRedirect(route('intern.dashboard'));
        $response->assertSessionHas('success');

        $attendance = Attendance::where('user_id', $intern->id)
            ->where('date', Carbon::today()->toDateString())
            ->first();

        $this->assertNotNull($attendance);
        $this->assertEquals('sakit', $attendance->sub_status);
        $this->assertNotNull($attendance->attachment_file);

        // Verifikasi berkas tersimpan di disk privat (storage/app/private)
        /** @var \Illuminate\Filesystem\FilesystemAdapter $localDisk */
        $localDisk = Storage::disk('local');
        $localDisk->assertExists($attendance->attachment_file);

        // Akses oleh pemilik sah -> 200 OK
        $downloadResponse = $this->actingAs($intern)->get(route('attendance.attachment', $attendance->id));
        $downloadResponse->assertOk();

        // Akses oleh pihak tidak berhak -> 403 Forbidden
        $otherIntern = User::factory()->create(['role' => 'intern']);
        $forbiddenResponse = $this->actingAs($otherIntern)->get(route('attendance.attachment', $attendance->id));
        $forbiddenResponse->assertForbidden();
    }

    /**
     * Test 10: Peserta Magang dan Admin Dapat Mengubah Kata Sandi Mandiri Secara Aman
     */
    public function test_intern_and_admin_can_change_password_securely(): void
    {
        $intern = User::where('email', 'magang@bps.go.id')->first();
        $admin = User::where('email', 'admin@bps.go.id')->first();

        // 1. Intern mencoba ganti password dengan kata sandi lama yang salah -> harus gagal
        $failedResponse = $this->actingAs($intern)->put(route('intern.profil.update'), [
            'name' => $intern->name,
            'current_password' => 'passwordsalah',
            'new_password' => 'PasswordBaru123!',
            'new_password_confirmation' => 'PasswordBaru123!',
        ]);
        $failedResponse->assertSessionHasErrors('current_password');

        // 2. Intern ganti password dengan kata sandi lama yang benar -> sukses
        $successResponse = $this->actingAs($intern)->put(route('intern.profil.update'), [
            'name' => $intern->name,
            'current_password' => 'password',
            'new_password' => 'PasswordBaru123!',
            'new_password_confirmation' => 'PasswordBaru123!',
        ]);
        $successResponse->assertSessionHas('success');
        $this->assertTrue(Hash::check('PasswordBaru123!', $intern->fresh()->password));

        // 3. Admin ganti password melalui settings.password -> sukses
        $adminResponse = $this->actingAs($admin)->put(route('admin.settings.password'), [
            'current_password' => 'password',
            'new_password' => 'AdminPasswordBaru2026!',
            'new_password_confirmation' => 'AdminPasswordBaru2026!',
        ]);
        $adminResponse->assertSessionHas('success');
        $this->assertTrue(Hash::check('AdminPasswordBaru2026!', $admin->fresh()->password));
    }

    /**
     * Test 11: Presensi Pulang (Check-Out) dan Penghitungan Durasi Jam Kerja
     */
    public function test_checkout_and_work_duration(): void
    {
        $intern = User::where('email', 'magang@bps.go.id')->first();
        Attendance::where('user_id', $intern->id)->where('date', Carbon::today()->toDateString())->delete();

        // Buat presensi masuk (Hadir)
        $attendance = Attendance::create([
            'user_id' => $intern->id,
            'date' => Carbon::today()->toDateString(),
            'status' => 'hadir',
            'sub_status' => 'hadir_kantor',
            'check_in_time' => '07:25:00',
            'latitude' => -7.597810,
            'longitude' => 110.950510,
            'distance_meters' => 12.5,
            'activity_description' => 'Aktivitas masuk kantor BPS Karanganyar.',
            'learning_obtained' => 'Pengolahan sensus penduduk.',
            'constraints_encountered' => '-',
            'is_confirmed' => true,
            'approval_status' => 'pending',
        ]);

        // Kirim presensi pulang di radius kantor
        $checkoutResponse = $this->actingAs($intern)->post(route('intern.absen.checkout'), [
            'latitude' => -7.597800,
            'longitude' => 110.950500,
        ]);

        $checkoutResponse->assertRedirect(route('intern.dashboard'));
        $checkoutResponse->assertSessionHas('success');

        $attendance->refresh();
        $this->assertNotNull($attendance->check_out_time);
        $this->assertNotNull($attendance->work_duration_formatted);
    }

    /**
     * Test 12: Device Binding Mencegah Titip Absen & Reset oleh Admin
     */
    public function test_device_binding_prevents_titip_absen_and_admin_can_reset(): void
    {
        $intern = User::where('email', 'magang@bps.go.id')->first();
        $intern->update(['device_id' => null, 'device_name' => null]);

        // 1. Login pertama dari HP milik sendiri -> Sukses terikat
        $login1 = $this->post(route('login.post'), [
            'email' => 'magang@bps.go.id',
            'password' => 'password',
            'device_id' => 'device_hp_sendiri_123',
            'device_name' => 'Samsung Galaxy A54',
        ]);
        $login1->assertRedirect(route('intern.dashboard'));
        $this->assertEquals('device_hp_sendiri_123', $intern->fresh()->device_id);

        $this->post(route('logout'));

        // 2. Teman mencoba login akun ini dari HP berbeda -> Akses Ditolak (Titip Absen Diblokir)
        $login2 = $this->post(route('login.post'), [
            'email' => 'magang@bps.go.id',
            'password' => 'password',
            'device_id' => 'device_hp_teman_456',
            'device_name' => 'Xiaomi Redmi Note',
        ]);
        $login2->assertSessionHasErrors('email');
        $this->assertGuest();

        // 3. Admin mereset kunci perangkat
        $admin = User::where('email', 'admin@bps.go.id')->first();
        $resetResponse = $this->actingAs($admin)->post(route('admin.interns.reset-device', $intern->id));
        $resetResponse->assertSessionHas('success');
        $this->assertNull($intern->fresh()->device_id);
    }

    /**
     * Test 13: Pembimbing BPS Menginput Nilai Evaluasi & Predikat Terhitung Otomatis
     */
    public function test_admin_can_evaluate_intern_and_compute_score(): void
    {
        $admin = User::where('email', 'admin@bps.go.id')->first();
        $intern = User::where('email', 'magang@bps.go.id')->first();

        $response = $this->actingAs($admin)->put(route('admin.evaluasi.update', $intern->id), [
            'discipline_score' => 90,
            'responsibility_score' => 88,
            'initiative_score' => 85,
            'teamwork_score' => 90,
            'work_quality_score' => 92,
            'notes' => 'Sangat rajin dan berintegritas tinggi selama bertugas di seksi IPDS.',
            'evaluation_date' => Carbon::today()->toDateString(),
        ]);

        $response->assertRedirect(route('admin.evaluasi.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('intern_evaluations', [
            'user_id' => $intern->id,
            'final_score' => 89.0,
            'predicate' => 'A (Sangat Baik)',
        ]);

        // Intern dapat melihat hasil evaluasinya
        $internView = $this->actingAs($intern)->get(route('intern.evaluasi'));
        $internView->assertOk();
        $internView->assertSee('A (Sangat Baik)');
    }
}

