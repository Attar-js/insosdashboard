<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_kelompok',
        'judul_kegiatan',
        'lokasi_kkn',
        'deskripsi_kegiatan',
        'nama_mitra',
        'lokasi_mitra',
        'dosen_id',
        'leader_id',
        'assigned_by',
        'assigned_at',
        'supervisor_approved_at',
        'assignment_note',
        'status',
        'catatan',
        'proposal_review_status',
        'proposal_review_note',
        'proposal_reviewed_at',
        'proposal_reviewed_by',
        'progress_verifikasi',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'supervisor_approved_at' => 'datetime',
        'proposal_reviewed_at' => 'datetime',
    ];

    public function dosen()
    {
        return $this->belongsTo(User::class, 'dosen_id');
    }

    public function groupLeader()
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    public function members()
    {
        return $this->hasMany(GroupMember::class);
    }

    public function activeMembers()
    {
        return $this->hasMany(GroupMember::class)->where('status', 'active');
    }

    public function cpmkRubric()
    {
        return $this->hasOne(GroupCpmkRubric::class);
    }

    public function isSupervisorApproved(): bool
    {
        return $this->status === 'active' && $this->supervisor_approved_at !== null;
    }

    public function isTimPenciriVerified(): bool
    {
        return (int) ($this->progress_verifikasi ?? 0) >= 100;
    }

    public function leaderNim(): ?string
    {
        if ($this->groupLeader?->nim) {
            return $this->groupLeader->nim;
        }

        $leaderMember = $this->members
            ->first(fn ($m) => $m->role === 'leader' && $m->mahasiswa);

        return $leaderMember?->mahasiswa?->nim;
    }
}
