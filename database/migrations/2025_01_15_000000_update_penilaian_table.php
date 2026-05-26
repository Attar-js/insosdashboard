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
        Schema::table('penilaian', function (Blueprint $table) {
            // Drop old columns
            $table->dropColumn(['nilai_kehadiran', 'nilai_tugas', 'nilai_praktikum', 'nilai_ujian']);
            
            // Add new columns
            $table->decimal('proposal_kegiatan', 5, 2)->nullable()->after('nilai_akhir');
            $table->decimal('peer_review', 5, 2)->nullable()->after('proposal_kegiatan');
            $table->decimal('laporan_akhir', 5, 2)->nullable()->after('peer_review');
            $table->decimal('presentasi_akhir', 5, 2)->nullable()->after('laporan_akhir');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penilaian', function (Blueprint $table) {
            // Drop new columns
            $table->dropColumn(['proposal_kegiatan', 'peer_review', 'laporan_akhir', 'presentasi_akhir']);
            
            // Add back old columns
            $table->decimal('nilai_kehadiran', 5, 2)->nullable()->after('nilai_akhir');
            $table->decimal('nilai_tugas', 5, 2)->nullable()->after('nilai_kehadiran');
            $table->decimal('nilai_praktikum', 5, 2)->nullable()->after('nilai_tugas');
            $table->decimal('nilai_ujian', 5, 2)->nullable()->after('nilai_praktikum');
        });
    }
}; 