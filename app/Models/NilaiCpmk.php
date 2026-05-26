<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NilaiCpmk extends Model
{
    use HasFactory;

    protected $table = 'nilai_cpmk';

    protected $fillable = [
        'nim_mahasiswa',
        'nama_mahasiswa',
        'judul_kegiatan',
        'file_name',
        'file_content',
        'file_mime_type',
        'file_size',
        'uploaded_by',
        'status',
        'catatan'
        // Removed uploaded_at from fillable
    ];

    protected $attributes = [
        'status' => 'pending',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'file_size' => 'integer',
    ];
    
    /**
     * Boot the model and add event listeners
     */
    protected static function boot()
    {
        parent::boot();
        
        // Set uploaded_at when creating
        static::creating(function ($model) {
            if (!$model->uploaded_at) {
                $model->uploaded_at = now();
            }
        });
    }

    /**
     * Get the user who uploaded the file
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by', 'username');
    }

    /**
     * Scope for filtering by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for filtering by NIM mahasiswa
     */
    public function scopeByNim($query, $nim)
    {
        return $query->where('nim_mahasiswa', $nim);
    }

    /**
     * Get formatted file size
     */
    public function getFormattedFileSizeAttribute()
    {
        if (!$this->file_size) {
            return 'Unknown';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }


} 