<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\KknPendaftar as Pendaftar;
use App\Models\Proposal;
use App\Models\LaporanAkhir;
use App\Models\Luaran;
use App\Models\Penilaian;
use App\Models\FormKesediaan;
use App\Models\PeerReview;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display dashboard with real-time data
     */
    public function index()
    {
        // Get real-time data
        $dashboardData = $this->getDashboardData();
        
        return view('dashboards.dashboard', compact('dashboardData'));
    }

    /**
     * Get real-time dashboard data
     */
    private function getDashboardData()
    {
        $now = Carbon::now();
        $startOfMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth();

        return [
            // Card 1: Total Pendaftar KKN
            'total_pendaftar' => [
                'current' => Pendaftar::count(),
                'previous' => Pendaftar::where('created_at', '<', $startOfMonth)->count(),
                'growth' => $this->calculateGrowth('pendaftar'),
                'status' => 'active'
            ],

            // Card 2: Total Proposal Disetujui
            'total_proposal_approved' => [
                'current' => Proposal::where('status', 'approved')->count(),
                'previous' => Proposal::where('status', 'approved')
                    ->where('created_at', '<', $startOfMonth)->count(),
                'growth' => $this->calculateGrowth('proposal_approved'),
                'status' => 'approved'
            ],

            // Card 3: Total Surat Kesediaan Dosen Disetujui
            'total_kesediaan_approved' => [
                'current' => \App\Models\FormKesediaan::where('status', 'approved')->count(),
                'previous' => \App\Models\FormKesediaan::where('status', 'approved')
                    ->where('created_at', '<', $startOfMonth)->count(),
                'growth' => $this->calculateGrowth('kesediaan_approved'),
                'status' => 'approved'
            ],

            // Card 4: Total Laporan Akhir Disetujui
            'total_laporan_approved' => [
                'current' => LaporanAkhir::where('status', 'approved')->count(),
                'previous' => LaporanAkhir::where('status', 'approved')
                    ->where('created_at', '<', $startOfMonth)->count(),
                'growth' => $this->calculateGrowth('laporan_approved'),
                'status' => 'approved'
            ],

            // Card 5: Total Luaran Disetujui
            'total_luaran_approved' => [
                'current' => Luaran::where('status', 'approved')->count(),
                'previous' => Luaran::where('status', 'approved')
                    ->where('created_at', '<', $startOfMonth)->count(),
                'growth' => $this->calculateGrowth('luaran_approved'),
                'status' => 'approved'
            ],

            // Card 6: Total Peer Review Disetujui
            'total_peer_review_approved' => [
                'current' => \App\Models\PeerReview::where('status', 'approved')->count(),
                'previous' => \App\Models\PeerReview::where('status', 'approved')
                    ->where('created_at', '<', $startOfMonth)->count(),
                'growth' => $this->calculateGrowth('peer_review_approved'),
                'status' => 'approved'
            ],

            // Chart Data: Progress Penilaian
            'progress_penilaian' => $this->getProgressPenilaianData(),

            // Summary Statistics
            'summary_stats' => [
                'total_artikel' => Luaran::where('artikel_link', '!=', null)->where('status', 'approved')->count(),
                'total_video' => Luaran::where('video_aftermovie', '!=', null)->where('status', 'approved')->count(),
                'total_dosen' => User::where('role', 'dosen')->count(),
                'total_mahasiswa' => User::where('role', 'mahasiswa')->count(),
            ],

            // Recent Activities
            'recent_activities' => $this->getRecentActivities(),

            // System Health
            'system_health' => [
                'server_status' => 'online',
                'database_connections' => DB::connection()->getPdo() ? 'connected' : 'disconnected',
                'last_backup' => Carbon::now()->subHours(2)->format('d/m/Y H:i'),
                'uptime' => '99.9%'
            ]
        ];
    }

    /**
     * Calculate growth percentage
     */
    private function calculateGrowth($type)
    {
        $current = 0;
        $previous = 0;

        switch ($type) {
            case 'pendaftar':
                $current = Pendaftar::where('created_at', '>=', Carbon::now()->startOfMonth())->count();
                $previous = Pendaftar::whereBetween('created_at', [
                    Carbon::now()->subMonth()->startOfMonth(),
                    Carbon::now()->subMonth()->endOfMonth()
                ])->count();
                break;

                    case 'proposal_approved':
            $current = Proposal::where('status', 'approved')
                ->where('created_at', '>=', Carbon::now()->startOfMonth())->count();
            $previous = Proposal::where('status', 'approved')
                ->whereBetween('created_at', [
                    Carbon::now()->subMonth()->startOfMonth(),
                    Carbon::now()->subMonth()->endOfMonth()
                ])->count();
            break;

            case 'kegiatan_aktif':
                $current = $this->getActiveKegiatanCount();
                $previous = $this->getActiveKegiatanCount(Carbon::now()->subMonth());
                break;

                    case 'luaran_approved':
            $current = Luaran::where('status', 'approved')
                ->where('created_at', '>=', Carbon::now()->startOfMonth())->count();
            $previous = Luaran::where('status', 'approved')
                ->whereBetween('created_at', [
                    Carbon::now()->subMonth()->startOfMonth(),
                    Carbon::now()->subMonth()->endOfMonth()
                ])->count();
            break;

            case 'kesediaan_approved':
            $current = \App\Models\FormKesediaan::where('status', 'approved')
                ->where('created_at', '>=', Carbon::now()->startOfMonth())->count();
            $previous = \App\Models\FormKesediaan::where('status', 'approved')
                ->whereBetween('created_at', [
                    Carbon::now()->subMonth()->startOfMonth(),
                    Carbon::now()->subMonth()->endOfMonth()
                ])->count();
            break;

            case 'laporan_approved':
            $current = LaporanAkhir::where('status', 'approved')
                ->where('created_at', '>=', Carbon::now()->startOfMonth())->count();
            $previous = LaporanAkhir::where('status', 'approved')
                ->whereBetween('created_at', [
                    Carbon::now()->subMonth()->startOfMonth(),
                    Carbon::now()->subMonth()->endOfMonth()
                ])->count();
            break;

            case 'peer_review_approved':
            $current = \App\Models\PeerReview::where('status', 'approved')
                ->where('created_at', '>=', Carbon::now()->startOfMonth())->count();
            $previous = \App\Models\PeerReview::where('status', 'approved')
                ->whereBetween('created_at', [
                    Carbon::now()->subMonth()->startOfMonth(),
                    Carbon::now()->subMonth()->endOfMonth()
                ])->count();
            break;
        }

        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    /**
     * Get active kegiatan count
     */
    private function getActiveKegiatanCount($date = null)
    {
        if (!$date) {
            $date = Carbon::now();
        }

        // Count active KKN activities (simplified logic)
        return Pendaftar::where('status', 'active')
            ->where('created_at', '<=', $date)
            ->where('created_at', '>=', $date->copy()->subMonths(6))
            ->count();
    }

    /**
     * Get pendaftar daily data for chart
     */
    private function getProgressPenilaianData()
    {
        $data = [];
        $labels = [];

        // Get last 14 days of data
        for ($i = 13; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dayStart = $date->copy()->startOfDay();
            $dayEnd = $date->copy()->endOfDay();

            $dailyPendaftar = Pendaftar::whereBetween('created_at', [$dayStart, $dayEnd])
                ->count();

            $data[] = $dailyPendaftar;
            $labels[] = $date->format('M d');
        }

        return [
            'data' => $data,
            'labels' => $labels,
            'target' => null, // No target for pendaftar
            'current_total' => Pendaftar::count()
        ];
    }



    /**
     * Get recent activities
     */
    private function getRecentActivities()
    {
        $activities = [];

        // Recent pendaftar
        $recentPendaftar = Pendaftar::latest()->take(3)->get();
        foreach ($recentPendaftar as $pendaftar) {
            $activities[] = [
                'type' => 'pendaftar',
                'message' => "Pendaftar baru: {$pendaftar->nama_lengkap}",
                'time' => $pendaftar->created_at->diffForHumans(),
                'icon' => 'fas fa-user-plus',
                'color' => 'primary'
            ];
        }

        // Recent approvals
        $recentApprovals = Proposal::where('status', 'approved')
            ->latest()->take(3)->get();
        foreach ($recentApprovals as $proposal) {
            $activities[] = [
                'type' => 'approval',
                'message' => "Proposal disetujui: {$proposal->judul_kegiatan}",
                'time' => $proposal->created_at->diffForHumans(),
                'icon' => 'fas fa-check-circle',
                'color' => 'success'
            ];
        }

        // Recent luaran
        $recentLuaran = Luaran::where('status', 'approved')
            ->latest()->take(3)->get();
        foreach ($recentLuaran as $luaran) {
            $activities[] = [
                'type' => 'luaran',
                'message' => "Luaran dipublikasi: {$luaran->judul_kegiatan}",
                'time' => $luaran->created_at->diffForHumans(),
                'icon' => 'fas fa-file-alt',
                'color' => 'info'
            ];
        }

        // Sort by time and take latest 5
        usort($activities, function($a, $b) {
            return strtotime($b['time']) - strtotime($a['time']);
        });

        return array_slice($activities, 0, 5);
    }

    /**
     * Get dashboard data via AJAX for real-time updates
     */
    public function getDashboardDataAjax()
    {
        $data = $this->getDashboardData();
        return response()->json($data);
    }
} 