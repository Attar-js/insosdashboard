<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeerReview extends Model
{
    use HasFactory;

    protected $table = 'peer_review';

    protected $fillable = [
        'judul_kegiatan',
        'file_name',
        'file_path',
        'user_nim',
        'status',
        'catatan',
    ];
}
