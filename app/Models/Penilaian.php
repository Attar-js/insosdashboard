<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penilaian extends Model
{
    use HasFactory;

    protected $table = 'penilaian';

    protected $fillable = [
        'mahasiswa_nim',
        'dosen_id',
        'proposal_kegiatan',
        'asistensi',
        'peer_review',
        'laporan_akhir',
        'presentasi_akhir',
        'pembimbing_lapangan',
        'nilai_akhir',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(User::class, 'mahasiswa_nim', 'nim');
    }

    public function dosen()
    {
        return $this->belongsTo(User::class, 'dosen_id');
    }
} 