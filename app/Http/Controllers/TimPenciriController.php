<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Models\GroupAssignment;
use App\Models\User;

class TimPenciriController extends Controller
{
    /**
     * Dashboard tim penciri
     */
    public function dashboard()
    {
        // Ambil data kelompok dari project-akhir
        $groups = $this->getGroupsFromProjectAkhir();
        
        // Statistik
        $totalGroups = count($groups);
        $verifiedGroups = collect($groups)->where('progress_verifikasi', 100)->count();
        $assignedGroups = GroupAssignment::count();
        $pendingAssignment = $verifiedGroups - $assignedGroups;
        
        return view('tim-penciri.dashboard', compact('groups', 'totalGroups', 'verifiedGroups', 'assignedGroups', 'pendingAssignment'));
    }

    /**
     * Halaman kelompok yang sudah 100% verified
     */
    public function verifiedGroups()
    {
        // Ambil data kelompok dari project-akhir
        $groups = $this->getGroupsFromProjectAkhir();
        
        // Filter hanya yang 100% verified
        $verifiedGroups = collect($groups)->filter(function ($group) {
            return $group['progress_verifikasi'] >= 100;
        });
        
        return view('tim-penciri.verified-groups', compact('verifiedGroups'));
    }

    /**
     * Form assignment dosen
     */
    public function assignForm($group_id)
    {
        // Ambil data kelompok dari project-akhir
        $groups = $this->getGroupsFromProjectAkhir();
        $group = collect($groups)->firstWhere('id', $group_id);
        
        if (!$group) {
            return redirect()->route('tim-penciri.verified-groups')->with('error', 'Kelompok tidak ditemukan');
        }
        
        // Ambil daftar dosen dari project-akhir
        $dosenList = $this->getDosenFromProjectAkhir();
        
        return view('tim-penciri.assign-form', compact('group', 'dosenList'));
    }

    /**
     * Simpan assignment dosen
     */
    public function assignDosen(Request $request, $group_id)
    {
        $request->validate([
            'dosen_id' => 'required',
            'assignment_note' => 'nullable|string|max:1000'
        ]);
        
        // Ambil data kelompok dari project-akhir
        $groups = $this->getGroupsFromProjectAkhir();
        $group = collect($groups)->firstWhere('id', $group_id);
        
        if (!$group) {
            return redirect()->route('tim-penciri.verified-groups')->with('error', 'Kelompok tidak ditemukan');
        }
        
        // Ambil data dosen
        $dosenList = $this->getDosenFromProjectAkhir();
        $dosen = collect($dosenList)->firstWhere('id', $request->dosen_id);
        
        if (!$dosen) {
            return redirect()->back()->with('error', 'Dosen tidak ditemukan');
        }
        
        // Simpan assignment
        GroupAssignment::create([
            'group_id' => $group_id,
            'group_name' => $group['nama_kelompok'],
            'group_members' => $group['members'],
            'judul_kegiatan' => $group['judul_kegiatan'],
            'lokasi_kkn' => $group['lokasi_kkn'],
            'nama_mitra' => $group['nama_mitra'] ?? null,
            'lokasi_mitra' => $group['lokasi_mitra'] ?? null,
            'dosen_id' => $request->dosen_id,
            'dosen_name' => $dosen['name'],
            'assigned_by' => Auth::id(),
            'assigned_at' => now(),
            'assignment_note' => $request->assignment_note,
            'status' => 'assigned',
            'progress_verified' => 100
        ]);
        
        // Kirim notifikasi ke project-akhir
        $this->sendAssignmentToProjectAkhir($group_id, $request->dosen_id);
        
        return redirect()->route('tim-penciri.verified-groups')->with('success', 'Kelompok berhasil diassign ke dosen');
    }

    /**
     * Halaman kelompok yang sudah diassign
     */
    public function assignedGroups()
    {
        $assignments = GroupAssignment::with('assignedBy')->get();
        
        return view('tim-penciri.assigned-groups', compact('assignments'));
    }

    /**
     * Ambil data kelompok dari project-akhir
     */
    private function getGroupsFromProjectAkhir()
    {
        try {
            $response = Http::get(\App\Helpers\DashboardHelper::getLandingApiUrl('groups/status-verifikasi'));
            
            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            // Fallback data untuk testing
            return [
                [
                    'id' => '1',
                    'nama_kelompok' => 'Kelompok A',
                    'judul_kegiatan' => 'Edukasi Digital',
                    'lokasi_kkn' => 'Jl. Kemayoran',
                    'nama_mitra' => 'SD Al-Azhar',
                    'lokasi_mitra' => 'Jl. Kemayoran',
                    'members' => [
                        ['name' => 'Ni Putu Dian Pramesti', 'nim' => '10221061']
                    ],
                    'progress_verifikasi' => 100
                ],
                [
                    'id' => '2',
                    'nama_kelompok' => 'Kelompok B',
                    'judul_kegiatan' => 'Peningkatan Gizi Balita',
                    'lokasi_kkn' => 'Jl. Menuju Syurga',
                    'nama_mitra' => 'Posyandu Kita',
                    'lokasi_mitra' => 'Jl. Menuju Syurga',
                    'members' => [
                        ['name' => 'Shello Juliano Julius', 'nim' => '10221041']
                    ],
                    'progress_verifikasi' => 100
                ]
            ];
        }
        
        return [];
    }

    /**
     * Ambil data dosen dari project-akhir
     */
    private function getDosenFromProjectAkhir()
    {
        try {
            $response = Http::get(\App\Helpers\DashboardHelper::getLandingApiUrl('dosen/list'));
            
            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            // Fallback data untuk testing
            return [
                [
                    'id' => '1',
                    'name' => 'Dr. Ahmad S.T., M.T.',
                    'email' => 'ahmad@test.com',
                    'nip' => '199208012019031010'
                ],
                [
                    'id' => '2',
                    'name' => 'Dr. Sarah M.Kom.',
                    'email' => 'sarah@test.com',
                    'nip' => '199208012019031011'
                ]
            ];
        }
        
        return [];
    }

    /**
     * Kirim assignment ke project-akhir
     */
    private function sendAssignmentToProjectAkhir($group_id, $dosen_id)
    {
        try {
            Http::post(\App\Helpers\DashboardHelper::getLandingApiUrl('dosen/assign-group'), [
                'group_id' => $group_id,
                'dosen_id' => $dosen_id
            ]);
        } catch (\Exception $e) {
            // Log error jika gagal kirim ke project-akhir
            \Log::error('Failed to send assignment to project-akhir: ' . $e->getMessage());
        }
    }
}
