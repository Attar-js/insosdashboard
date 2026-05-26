<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class NilaiDashboardController extends Controller
{
    /**
     * Menampilkan dashboard nilai mahasiswa
     */
    public function index()
    {
        $user = Auth::user();
        
        // Ambil data nilai dari database project-akhir
        $nilaiMahasiswa = DB::connection('project_akhir')
            ->table('penilaian')
            ->join('users', 'penilaian.mahasiswa_nim', '=', 'users.nim')
            ->join('users as dosen', 'penilaian.dosen_id', '=', 'dosen.id')
            ->select([
                'penilaian.*',
                'users.name as mahasiswa_name',
                'users.nim',
                'dosen.name as dosen_name'
            ])
            ->orderBy('penilaian.tanggal_penilaian', 'desc')
            ->get();
        
        // Statistik nilai
        $totalMahasiswa = $nilaiMahasiswa->count();
        $nilaiRataRata = $totalMahasiswa > 0 ? $nilaiMahasiswa->avg('nilai_akhir') : 0;
        $nilaiTertinggi = $totalMahasiswa > 0 ? $nilaiMahasiswa->max('nilai_akhir') : 0;
        $nilaiTerendah = $totalMahasiswa > 0 ? $nilaiMahasiswa->min('nilai_akhir') : 0;
        
        // Distribusi nilai
        $distribusiNilai = [
            'A' => $nilaiMahasiswa->where('nilai_akhir', '>=', 85)->count(),
            'B' => $nilaiMahasiswa->whereBetween('nilai_akhir', [75, 84.99])->count(),
            'C' => $nilaiMahasiswa->whereBetween('nilai_akhir', [65, 74.99])->count(),
            'D' => $nilaiMahasiswa->whereBetween('nilai_akhir', [50, 64.99])->count(),
            'E' => $nilaiMahasiswa->where('nilai_akhir', '<', 50)->count(),
        ];
        
        // Top 5 mahasiswa dengan nilai tertinggi
        $topMahasiswa = $nilaiMahasiswa->sortByDesc('nilai_akhir')->take(5);
        
        // Nilai per komponen
        $rataRataKomponen = [
            'proposal' => $nilaiMahasiswa->avg('proposal_kegiatan'),
            'peer_review' => $nilaiMahasiswa->avg('peer_review'),
            'laporan_akhir' => $nilaiMahasiswa->avg('laporan_akhir'),
            'presentasi_akhir' => $nilaiMahasiswa->avg('presentasi_akhir'),
        ];
        
        return view('dashboards.nilai-dashboard', compact(
            'nilaiMahasiswa',
            'totalMahasiswa',
            'nilaiRataRata',
            'nilaiTertinggi',
            'nilaiTerendah',
            'distribusiNilai',
            'topMahasiswa',
            'rataRataKomponen'
        ));
    }
    
    /**
     * Menampilkan detail nilai mahasiswa
     */
    public function detail($nim)
    {
        $nilaiDetail = DB::connection('project_akhir')
            ->table('penilaian')
            ->join('users', 'penilaian.mahasiswa_nim', '=', 'users.nim')
            ->join('users as dosen', 'penilaian.dosen_id', '=', 'dosen.id')
            ->where('penilaian.mahasiswa_nim', $nim)
            ->select([
                'penilaian.*',
                'users.name as mahasiswa_name',
                'users.nim',
                'dosen.name as dosen_name'
            ])
            ->first();
        
        if (!$nilaiDetail) {
            abort(404, 'Nilai mahasiswa tidak ditemukan');
        }
        
        return view('dashboards.nilai-detail', compact('nilaiDetail'));
    }
    
    /**
     * API untuk mendapatkan data nilai dalam format JSON
     */
    public function getNilaiData()
    {
        $nilaiMahasiswa = DB::connection('project_akhir')
            ->table('penilaian')
            ->join('users', 'penilaian.mahasiswa_nim', '=', 'users.nim')
            ->join('users as dosen', 'penilaian.dosen_id', '=', 'dosen.id')
            ->select([
                'penilaian.*',
                'users.name as mahasiswa_name',
                'users.nim',
                'dosen.name as dosen_name'
            ])
            ->orderBy('penilaian.tanggal_penilaian', 'desc')
            ->get();
        
        return response()->json($nilaiMahasiswa);
    }
} 