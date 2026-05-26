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
        Schema::create('penilaian', function (Blueprint $table) {
            $table->id();
            $table->string('mahasiswa_nim');
            $table->unsignedBigInteger('dosen_id');
            $table->decimal('nilai_akhir', 5, 2)->nullable();
            $table->decimal('nilai_kehadiran', 5, 2)->nullable();
            $table->decimal('nilai_tugas', 5, 2)->nullable();
            $table->decimal('nilai_praktikum', 5, 2)->nullable();
            $table->decimal('nilai_ujian', 5, 2)->nullable();
            $table->text('catatan')->nullable();
            $table->date('tanggal_penilaian')->nullable();
            $table->string('file_nilai')->nullable(); // File PDF nilai
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->text('komentar')->nullable();
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('mahasiswa_nim')->references('nim')->on('users')->onDelete('cascade');
            $table->foreign('dosen_id')->references('id')->on('users')->onDelete('cascade');
            
            // Unique constraint
            $table->unique(['mahasiswa_nim', 'dosen_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penilaian');
    }
};
