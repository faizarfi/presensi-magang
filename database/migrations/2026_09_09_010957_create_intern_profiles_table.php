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
        Schema::create('intern_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('nim_nisn')->nullable();
            $table->string('institution'); // Nama Kampus / Sekolah
            $table->string('major'); // Jurusan / Program Studi
            $table->string('division')->default('Subbagian Umum'); // Seksi di BPS Karanganyar
            $table->string('mentor_name')->nullable(); // Nama Pembimbing Lapangan BPS
            $table->date('start_date');
            $table->date('end_date');
            $table->text('address')->nullable();
            $table->text('bio')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('intern_profiles');
    }
};
