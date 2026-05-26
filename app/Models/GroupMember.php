<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupMember extends Model
{
    protected $fillable = [
        'group_id',
        'mahasiswa_id',
        'role',
        'status',
        'dropped_at',
        'drop_reason',
    ];

    protected $casts = [
        'dropped_at' => 'datetime',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function mahasiswa()
    {
        return $this->belongsTo(User::class, 'mahasiswa_id');
    }

    public function isLeader(): bool
    {
        return $this->role === 'leader';
    }

    public function peranLabel(): string
    {
        return $this->isLeader() ? 'Ketua' : 'Anggota';
    }
}
