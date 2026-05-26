<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KknPendaftar extends Model
{
    use HasFactory;

    protected $table = 'kkn_pendaftar';
    
    protected $fillable = [
        'judul_kegiatan',
        'mitra',
        'lokasi_mitra',
        'file_path',
        'file_name',
        'file_content',
        'file_mime_type',
        'file_size',
        'status',
        'catatan_verifikasi',
        'tanggal_verifikasi',
        'status_verifikasi',
        'user_nim'
    ];

    protected $casts = [
        'tanggal_verifikasi' => 'datetime',
    ];

    public function anggota()
    {
        return $this->hasMany(KknAnggota::class, 'kkn_pendaftar_id');
    }

    public function getJumlahAnggotaAttribute()
    {
        return $this->anggota()->count();
    }

    public function getKetuaAttribute()
    {
        return $this->anggota()->where('peran', 'Ketua')->first();
    }

    public function getTanggalDaftarAttribute()
    {
        return $this->created_at ? $this->created_at->format('Y-m-d') : null;
    }
} 