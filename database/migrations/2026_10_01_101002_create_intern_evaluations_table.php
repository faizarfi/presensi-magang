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
        Schema::create('intern_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->foreignId('evaluator_id')->nullable()->constrained('users')->onDelete('set null');
            $table->decimal('discipline_score', 5, 2)->default(0);       // Kedisiplinan & Ketepatan Waktu (0-100)
            $table->decimal('responsibility_score', 5, 2)->default(0);   // Tanggung Jawab & Integritas (0-100)
            $table->decimal('initiative_score', 5, 2)->default(0);       // Inisiatif & Kreativitas (0-100)
            $table->decimal('teamwork_score', 5, 2)->default(0);         // Kerjasama Tim & Komunikasi (0-100)
            $table->decimal('work_quality_score', 5, 2)->default(0);     // Kualitas Capaian Tugas & Laporan (0-100)
            $table->decimal('final_score', 5, 2)->default(0);            // Rata-rata akhir (0-100)
            $table->string('predicate', 20)->default('C');               // A (Sangat Baik), B (Baik), dll
            $table->text('notes')->nullable();                           // Ulasan / Catatan Pembimbing BPS
            $table->date('evaluation_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('intern_evaluations');
    }
};
