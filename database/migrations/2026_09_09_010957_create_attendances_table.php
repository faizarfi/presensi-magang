<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('date')->index();
            $table->enum('status', ['hadir', 'tidak_hadir_keterangan', 'tidak_hadir_tanpa_keterangan']);
            $table->string('sub_status')->nullable(); // 'sakit', 'izin', 'dinas_luar', 'alpa_otomatis', 'hadir_kantor'
            $table->time('check_in_time')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('distance_meters', 10, 2)->nullable();
            $table->string('location_address')->nullable();
            $table->text('activity_description')->nullable(); // Uraian Aktivitas
            $table->text('learning_obtained')->nullable(); // Pembelajaran yang diperoleh
            $table->text('constraints_encountered')->nullable(); // Kendala yang dialami
            $table->string('attachment_file')->nullable(); // Surat izin / dokter
            $table->string('photo')->nullable(); // Foto selfie / dokumentasi di BPS
            $table->boolean('is_confirmed')->default(false); // Centang pernyataan laporan sudah benar
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
