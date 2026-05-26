<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Group;

class GroupController extends Controller
{
    // Menampilkan detail kelompok (untuk halaman billing.blade.php)
    public function show($id)
    {
        $group = Group::findOrFail($id);
        $members = $group->members;
        return view('special-pages.billing', compact('group', 'members'));

    }


    // Memverifikasi kelompok
    public function verify($id)
    {
        $group = Group::findOrFail($id);
        $group->is_verified = true;
        $group->save();

        return redirect()->back()->with('success', 'Kelompok berhasil diverifikasi.');
    }
}
