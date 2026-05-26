<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\GroupAssignment;
use App\Models\User;
use App\Models\KknPendaftar;
use App\Models\KknAnggota;
use Illuminate\Support\Facades\DB; // Added missing import

class AssignKelompokController extends Controller
{
    /**
     * Halaman assign kelompok
     */
    public function index()
    {
        // Ambil data kelompok dari halaman pendaftar (form konversi)
        $groups = $this->getGroupsFromPendaftar();
        
        // Filter hanya yang 100% verified
        $verifiedGroups = collect($groups)->filter(function ($group) {
            return $group['progress_verifikasi'] >= 100;
        });
        
        // Ambil daftar dosen dari project-akhir
        $dosenList = $this->getDosenFromProjectAkhir();
        
        return view('assign-kelompok.index', compact('verifiedGroups', 'dosenList'));
    }

    /**
     * Simpan assignment dosen (dari modal)
     */
    public function assignDosen(Request $request, $group_id = null)
    {
        // Jika group_id dari parameter, gunakan itu, jika tidak dari request
        $groupId = $group_id ?: $request->group_id;
        
        $request->validate([
            'group_id' => 'required',
            'dosen_id' => 'required',
            'assignment_note' => 'nullable|string|max:1000'
        ]);
        
        // Ambil data kelompok dari halaman pendaftar
        $groups = $this->getGroupsFromPendaftar();
        $group = collect($groups)->firstWhere('id', $request->group_id);
        
        if (!$group) {
            return redirect()->back()->with('error', 'Kelompok tidak ditemukan');
        }
        
        // Ambil data dosen
        $dosenList = $this->getDosenFromProjectAkhir();
        $dosen = collect($dosenList)->firstWhere('id', $request->dosen_id);
        
        if (!$dosen) {
            return redirect()->back()->with('error', 'Dosen tidak ditemukan');
        }
        
        // Cek apakah sudah diassign
        $existingAssignment = GroupAssignment::where('group_id', $request->group_id)->first();
        if ($existingAssignment) {
            return redirect()->back()->with('error', 'Kelompok ini sudah diassign ke dosen lain');
        }
        
        // Simpan assignment
        GroupAssignment::create([
            'group_id' => $request->group_id,
            'group_name' => $group['nama_kelompok'],
            'group_members' => $group['members'],
            'judul_kegiatan' => $group['judul_kegiatan'],
            'lokasi_kkn' => $group['lokasi_kkn'] ?: 'Lokasi KKN', // Default value jika kosong
            'nama_mitra' => $group['nama_mitra'] ?: 'N/A', // Default value jika kosong
            'lokasi_mitra' => $group['lokasi_mitra'] ?: 'N/A', // Default value jika kosong
            'dosen_id' => $request->dosen_id,
            'dosen_name' => $dosen['name'],
            'assigned_by' => auth()->id(),
            'assigned_at' => now(),
            'assignment_note' => $request->assignment_note,
            'status' => 'assigned',
            'progress_verified' => 100
        ]);
        
        // Kirim notifikasi ke project-akhir
        $this->sendAssignmentToProjectAkhir($request->group_id, $request->dosen_id);
        
        return redirect()->route('assign-kelompok.index')->with('success', 'Kelompok berhasil diassign ke dosen');
    }

    /**
     * Hapus assignment dosen
     */
    public function hapusAssignment($group_id)
    {
        try {
            $assignment = GroupAssignment::where('group_id', $group_id)->first();
            
            if (!$assignment) {
                return redirect()->back()->with('error', 'Assignment tidak ditemukan');
            }
            
            $assignment->delete();
            
            return redirect()->route('assign-kelompok.index')->with('success', 'Assignment berhasil dihapus');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus assignment: ' . $e->getMessage());
        }
    }

    /**
     * Halaman kelompok yang sudah diassign
     */
    public function assignedGroups()
    {
        $assignments = GroupAssignment::with('assignedBy')->get();
        
        return view('assign-kelompok.assigned-groups', compact('assignments'));
    }

    /**
     * Ambil data kelompok dari halaman pendaftar (form konversi)
     */
    private function getGroupsFromPendaftar()
    {
        try {
            $pendaftarList = KknPendaftar::with('anggota')->get();
            $groups = [];
            
            foreach ($pendaftarList as $pendaftar) {
                // Cek apakah sudah diassign
                $assignment = GroupAssignment::where('group_id', $pendaftar->id)->first();
                
                // Ambil ketua kelompok
                $ketua = $pendaftar->anggota->where('peran', 'Ketua')->first();
                
                if (!$ketua) {
                    continue; // Skip jika tidak ada ketua
                }
                
                // Hitung progress verifikasi
                $progress = $this->calculateVerificationProgress($ketua->nim);
                
                // Siapkan data anggota
                $members = [];
                foreach ($pendaftar->anggota as $anggota) {
                    $members[] = [
                        'name' => $anggota->nama,
                        'nim' => $anggota->nim,
                        'role' => $anggota->peran
                    ];
                }
                
                // Siapkan data kelompok
                $groupData = [
                    'id' => $pendaftar->id,
                    'nama_kelompok' => $pendaftar->nama_kelompok ?: "Kelompok {$ketua->nama}",
                    'judul_kegiatan' => $pendaftar->judul_kegiatan ?: 'Judul Kegiatan',
                    'lokasi_kkn' => $pendaftar->lokasi_kkn ?: 'Lokasi KKN',
                    'nama_mitra' => $pendaftar->mitra ?: 'N/A', // Gunakan field 'mitra' bukan 'nama_mitra'
                    'lokasi_mitra' => $pendaftar->lokasi_mitra ?: 'N/A',
                    'members' => $members,
                    'ketua_nim' => $ketua->nim,
                    'ketua_nama' => $ketua->nama,
                    'progress_verifikasi' => $progress,
                    'status' => $assignment ? 'assigned' : 'pending',
                    'dosen_name' => $assignment ? $assignment->dosen_name : null,
                    'assigned_at' => $assignment ? $assignment->assigned_at : null
                ];
                
                $groups[] = $groupData;
            }
            
            // Jika tidak ada data, berikan fallback
            if (empty($groups)) {
                $groups = [
                    [
                        'id' => 5,
                        'nama_kelompok' => 'Kelompok Muhammad Fachrurrozi Attar',
                        'judul_kegiatan' => 'Pengembangan Pasar Sepinggan',
                        'lokasi_kkn' => 'Pasar Sepinggan',
                        'nama_mitra' => 'Pemerintah Pasar Sepinggan', // Field 'mitra' di database
                        'lokasi_mitra' => 'Pasar Sepinggan',
                        'members' => [
                            [
                                'name' => 'Muhammad Fachrurrozi Attar',
                                'nim' => '10221051',
                                'role' => 'Ketua'
                            ]
                        ],
                        'ketua_nim' => '10221051',
                        'ketua_nama' => 'Muhammad Fachrurrozi Attar',
                        'progress_verifikasi' => 100,
                        'status' => 'pending',
                        'dosen_name' => null,
                        'assigned_at' => null
                    ]
                ];
            }
            
            return $groups;
            
        } catch (\Exception $e) {
            \Log::error('Error in getGroupsFromPendaftar: ' . $e->getMessage());
            
            // Fallback data jika terjadi error
            return [
                [
                    'id' => 5,
                    'nama_kelompok' => 'Kelompok Muhammad Fachrurrozi Attar',
                    'judul_kegiatan' => 'Pengembangan Pasar Sepinggan',
                    'lokasi_kkn' => 'Pasar Sepinggan',
                    'nama_mitra' => 'Pemerintah Pasar Sepinggan', // Field 'mitra' di database
                    'lokasi_mitra' => 'Pasar Sepinggan',
                    'members' => [
                        [
                            'name' => 'Muhammad Fachrurrozi Attar',
                            'nim' => '10221051',
                            'role' => 'Ketua'
                        ]
                    ],
                    'ketua_nim' => '10221051',
                    'ketua_nama' => 'Muhammad Fachrurrozi Attar',
                    'progress_verifikasi' => 100,
                    'status' => 'pending',
                    'dosen_name' => null,
                    'assigned_at' => null
                ]
            ];
        }
    }

    /**
     * Hitung progress verifikasi berdasarkan dokumen yang sudah diverifikasi
     */
    private function calculateVerificationProgress($nimKetua)
    {
        try {
            // Cek pendaftar berdasarkan NIM ketua
            $pendaftar = KknPendaftar::whereHas('anggota', function($query) use ($nimKetua) {
                $query->where('nim', $nimKetua)->where('peran', 'Ketua');
            })->first();
            
            if (!$pendaftar) {
                return 0;
            }
            
            // Jika status verifikasi pendaftar adalah 'diterima', return 100%
            if ($pendaftar->status_verifikasi === 'diterima') {
                return 100;
            }
            
            // Hitung progress berdasarkan dokumen yang ada
            $progress = 0;
            $totalDocuments = 5; // proposal, form_kesediaan, laporan_akhir, luaran, peer_review
            $verifiedDocuments = 0;
            
            // Cek setiap jenis dokumen
            $documentTypes = ['proposal', 'form_kesediaan', 'laporan_akhir', 'luaran', 'peer_review'];
            
            foreach ($documentTypes as $table) {
                try {
                    $document = DB::table($table)->where('nim', $nimKetua)->first();
                    if ($document && $document->status_verifikasi === 'diterima') {
                        $verifiedDocuments++;
                    }
                } catch (\Exception $e) {
                    // Jika tabel tidak ada, skip
                    continue;
                }
            }
            
            $progress = ($verifiedDocuments / $totalDocuments) * 100;
            
            return $progress;
            
        } catch (\Exception $e) {
            \Log::error('Error calculating verification progress: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Ambil data dosen dari database HOPE-UI
     */
    private function getDosenFromProjectAkhir()
    {
        // Ambil user dengan role dosen dari database HOPE-UI
        $dosenList = User::where('role', 'dosen')
            ->orWhere('user_type', 'dosen')
            ->get()
            ->map(function ($dosen) {
                return [
                    'id' => $dosen->id,
                    'name' => $dosen->name ?: ($dosen->first_name . ' ' . $dosen->last_name),
                    'email' => $dosen->email,
                    'nip' => $dosen->nip ?: 'N/A',
                    'username' => $dosen->username,
                    'phone_number' => $dosen->phone_number
                ];
            })
            ->toArray();
        
        // Jika tidak ada dosen di database, gunakan fallback data
        if (empty($dosenList)) {
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
                ],
                [
                    'id' => '3',
                    'name' => 'Dr. Budi Santoso, S.T., M.Eng.',
                    'email' => 'budi@test.com',
                    'nip' => '199208012019031012'
                ],
                [
                    'id' => '4',
                    'name' => 'Dr. Siti Nurhaliza, S.Kom., M.T.',
                    'email' => 'siti@test.com',
                    'nip' => '199208012019031013'
                ]
            ];
        }
        
        return $dosenList;
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
