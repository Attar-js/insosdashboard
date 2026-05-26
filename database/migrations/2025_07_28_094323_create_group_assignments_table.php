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
        Schema::create('group_assignments', function (Blueprint $table) {
            $table->id();
            $table->string('group_id')->comment('ID kelompok dari project-akhir');
            $table->string('group_name')->comment('Nama kelompok');
            $table->text('group_members')->comment('Anggota kelompok (JSON)');
            $table->text('judul_kegiatan')->comment('Judul kegiatan');
            $table->string('lokasi_kkn')->comment('Lokasi KKN');
            $table->string('nama_mitra')->nullable()->comment('Nama mitra/partner');
            $table->string('lokasi_mitra')->nullable()->comment('Lokasi mitra');
            $table->unsignedBigInteger('dosen_id')->nullable()->comment('ID dosen dari project-akhir');
            $table->string('dosen_name')->nullable()->comment('Nama dosen');
            $table->unsignedBigInteger('assigned_by')->nullable()->comment('ID tim penciri');
            $table->timestamp('assigned_at')->nullable()->comment('Waktu assignment');
            $table->text('assignment_note')->nullable()->comment('Catatan assignment');
            $table->string('status')->default('assigned')->comment('Status: assigned, approved, rejected');
            $table->integer('progress_verified')->default(100)->comment('Progress verifikasi (100%)');
            $table->timestamps();
            
            $table->foreign('assigned_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_assignments');
    }
};
