<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'group_name',
        'group_members',
        'judul_kegiatan',
        'lokasi_kkn',
        'nama_mitra',
        'lokasi_mitra',
        'dosen_id',
        'dosen_name',
        'assigned_by',
        'assigned_at',
        'assignment_note',
        'status',
        'progress_verified'
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'group_members' => 'array'
    ];

    // Relationships
    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    // Helper methods
    public function isAssigned()
    {
        return $this->status === 'assigned' || $this->status === 'approved';
    }

    public function isFullyVerified()
    {
        return $this->progress_verified >= 100;
    }
}
