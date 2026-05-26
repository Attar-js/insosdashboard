<x-app-layout :assets="$assets ?? []">
<div class="row">
    <!-- Top Banner -->


    <!-- KPI Cards Grid - 2 Rows Layout -->
    <div class="col-12 mb-4">
        <!-- Row 1: Top 3 Cards -->
        <div class="row g-3 mb-3">
            <!-- Card 1: Total Pendaftar -->
            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6">
                <div class="card kpi-card h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center">
                            <div class="kpi-icon bg-primary-gradient me-3">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="kpi-content">
                                <h3 class="kpi-number mb-1" id="total-pendaftar">{{ number_format($dashboardData['total_pendaftar']['current']) }}</h3>
                                <p class="kpi-label mb-0">Total Pendaftar</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 2: Proposal Disetujui -->
            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6">
                <div class="card kpi-card h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center">
                            <div class="kpi-icon bg-success-gradient me-3">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <div class="kpi-content">
                                <h3 class="kpi-number mb-1" id="total-proposal">{{ number_format($dashboardData['total_proposal_approved']['current']) }}</h3>
                                <p class="kpi-label mb-0">Proposal Disetujui</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 3: Kesediaan Dosen -->
            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6">
                <div class="card kpi-card h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center">
                            <div class="kpi-icon bg-info-gradient me-3">
                                <i class="fas fa-user-tie"></i>
                            </div>
                            <div class="kpi-content">
                                <h3 class="kpi-number mb-1" id="total-kesediaan">{{ number_format($dashboardData['total_kesediaan_approved']['current']) }}</h3>
                                <p class="kpi-label mb-0">Kesediaan Dosen</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 2: Bottom 3 Cards -->
        <div class="row g-3">
            <!-- Card 4: Laporan Akhir -->
            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6">
                <div class="card kpi-card h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center">
                            <div class="kpi-icon bg-warning-gradient me-3">
                                <i class="fas fa-file-text"></i>
                            </div>
                            <div class="kpi-content">
                                <h3 class="kpi-number mb-1" id="total-laporan">{{ number_format($dashboardData['total_laporan_approved']['current']) }}</h3>
                                <p class="kpi-label mb-0">Laporan Akhir</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 5: Luaran Disetujui -->
            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6">
                <div class="card kpi-card h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center">
                            <div class="kpi-icon bg-secondary-gradient me-3">
                                <i class="fas fa-star"></i>
                            </div>
                            <div class="kpi-content">
                                <h3 class="kpi-number mb-1" id="total-luaran">{{ number_format($dashboardData['total_luaran_approved']['current']) }}</h3>
                                <p class="kpi-label mb-0">Luaran Disetujui</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 6: Peer Review -->
            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6">
                <div class="card kpi-card h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center">
                            <div class="kpi-icon bg-dark-gradient me-3">
                                <i class="fas fa-eye"></i>
                            </div>
                            <div class="kpi-content">
                                <h3 class="kpi-number mb-1" id="total-peer-review">{{ number_format($dashboardData['total_peer_review_approved']['current']) }}</h3>
                                <p class="kpi-label mb-0">Peer Review</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pendaftar Chart -->
    <div class="col-xl-12 col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Statistik Pendaftar Harian</h5>
                <div class="d-flex align-items-center">
                    <div class="d-flex align-items-center me-3">
                        <span class="badge bg-primary me-2">●</span>
                        <small>Total Pendaftar per Hari</small>
                    </div>
                    <div class="d-flex align-items-center me-3">
                        <span class="badge bg-success me-2">●</span>
                        <small>Total: {{ $dashboardData['progress_penilaian']['current_total'] }} Pendaftar</small>
                    </div>
                    <div class="d-flex align-items-center me-3">
                        <span class="badge bg-info me-2">●</span>
                        <small>Rata-rata: {{ round($dashboardData['progress_penilaian']['current_total'] / 14, 1) }} per Hari</small>
                    </div>
                    <select class="form-select form-select-sm" style="width: auto;">
                        <option>14 Hari Terakhir</option>
                        <option>7 Hari Terakhir</option>
                        <option>Hari Ini</option>
                    </select>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-xl-9 col-lg-8">
                        <canvas id="progressChart" height="120"></canvas>
                    </div>
                    <div class="col-xl-3 col-lg-4">
                        <div class="chart-info">
                            <div class="mb-4">
                                <h6 class="text-muted mb-2">Ringkasan</h6>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Hari Tertinggi:</span>
                                    <strong>{{ max($dashboardData['progress_penilaian']['data']) }} pendaftar</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Hari Terendah:</span>
                                    <strong>{{ min($dashboardData['progress_penilaian']['data']) }} pendaftar</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Total Periode:</span>
                                    <strong>{{ $dashboardData['progress_penilaian']['current_total'] }} pendaftar</strong>
                                </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <!-- User Statistics -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Statistik User</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded-circle bg-primary p-2 me-3">
                                <i class="fas fa-user-graduate text-white"></i>
                            </div>
                            <div>
                                <h4 class="mb-1">{{ number_format($dashboardData['summary_stats']['total_mahasiswa']) }}</h4>
                                <p class="mb-0 text-muted">Total Mahasiswa</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded-circle bg-success p-2 me-3">
                                <i class="fas fa-user-tie text-white"></i>
                            </div>
                            <div>
                                <h4 class="mb-1">{{ number_format($dashboardData['summary_stats']['total_dosen']) }}</h4>
                                <p class="mb-0 text-muted">Total Dosen</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Aktivitas Terbaru</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($dashboardData['recent_activities'] as $activity)
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <div class="rounded-circle bg-{{ $activity['color'] }} p-2">
                                    <i class="{{ $activity['icon'] }} text-white"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <p class="mb-1">{{ $activity['message'] }}</p>
                                <small class="text-muted">{{ $activity['time'] }}</small>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>


                

                

</div>

<style>
.chart-info {
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    height: 100%;
}

.chart-info h6 {
    font-size: 0.875rem;
    font-weight: 600;
}

.chart-info .d-flex {
    font-size: 0.875rem;
}

.chart-info strong {
    color: #495057;
}

/* KPI Cards Styles */
.kpi-card {
    border: none;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    position: relative;
    overflow: hidden;
}

.kpi-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--card-color) 0%, var(--card-color-light) 100%);
}

.kpi-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 40px rgba(0,0,0,0.15);
}

.kpi-icon {
    width: 60px;
    height: 60px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
    position: relative;
    overflow: hidden;
}

.kpi-icon::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0.1) 100%);
    border-radius: 16px;
}

.kpi-content {
    flex: 1;
}

.kpi-number {
    font-size: 28px;
    font-weight: 700;
    color: #2c3e50;
    margin: 0;
    line-height: 1.2;
}

.kpi-label {
    font-size: 14px;
    font-weight: 500;
    color: #6c757d;
    margin: 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Gradient Backgrounds */
.bg-primary-gradient {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    --card-color: #007bff;
    --card-color-light: #0056b3;
}

.bg-success-gradient {
    background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
    --card-color: #28a745;
    --card-color-light: #1e7e34;
}

.bg-info-gradient {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    --card-color: #17a2b8;
    --card-color-light: #138496;
}

.bg-warning-gradient {
    background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
    --card-color: #ffc107;
    --card-color-light: #e0a800;
}

.bg-secondary-gradient {
    background: linear-gradient(135deg, #6c757d 0%, #545b62 100%);
    --card-color: #6c757d;
    --card-color-light: #545b62;
}

.bg-dark-gradient {
    background: linear-gradient(135deg, #343a40 0%, #212529 100%);
    --card-color: #343a40;
    --card-color-light: #212529;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .kpi-card {
        margin-bottom: 1rem;
    }
    
    .kpi-icon {
        width: 50px;
        height: 50px;
        font-size: 20px;
    }
    
    .kpi-number {
        font-size: 24px;
    }
    
    .kpi-label {
        font-size: 12px;
    }
}



.card-slide {
    border: none;
    box-shadow: 0 0 20px rgba(0,0,0,0.1);
    border-radius: 15px;
    transition: all 0.3s ease;
}

.card-slide:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}



/* Swiper Navigation */
.swiper-button {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 40px;
    height: 40px;
    background: rgba(0,123,255,0.8);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    cursor: pointer;
    transition: all 0.3s ease;
    z-index: 10;
}

.swiper-button:hover {
    background: rgba(0,123,255,1);
    transform: translateY(-50%) scale(1.1);
}

.swiper-button-next {
    right: 10px;
}

.swiper-button-prev {
    left: 10px;
}

.swiper-button::before {
    content: '';
    width: 0;
    height: 0;
    border: 6px solid transparent;
}

.swiper-button-next::before {
    border-left-color: white;
    margin-left: 2px;
}

.swiper-button-prev::before {
    border-right-color: white;
    margin-right: 2px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .swiper-slide {
        width: 280px;
    }
    
    .progress-widget {
        flex-direction: column;
        text-align: center;
        gap: 15px;
    }
    
    .circle-progress {
        width: 60px;
        height: 60px;
        font-size: 14px;
    }
}
</style>



<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Pendaftar Chart
const ctx = document.getElementById('progressChart').getContext('2d');
const progressChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: @json($dashboardData['progress_penilaian']['labels']),
        datasets: [{
            label: 'Total Pendaftar per Hari',
            data: @json($dashboardData['progress_penilaian']['data']),
            backgroundColor: 'rgba(0, 123, 255, 0.8)',
            borderColor: '#007bff',
            borderWidth: 1,
            borderRadius: 4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1,
                    precision: 0
                }
            }
        }
    }
});

// Initialize dashboard
document.addEventListener('DOMContentLoaded', function() {
    console.log('Dashboard initialized successfully');
});

// Real-time updates every 30 seconds
setInterval(function() {
    fetch('/dashboard/data')
        .then(response => response.json())
        .then(data => {
            // Update KPI cards
            document.getElementById('total-pendaftar').textContent = data.total_pendaftar.current.toLocaleString();
            document.getElementById('total-proposal').textContent = data.total_proposal_approved.current.toLocaleString();
            document.getElementById('total-kesediaan').textContent = data.total_kesediaan_approved.current.toLocaleString();
            document.getElementById('total-laporan').textContent = data.total_laporan_approved.current.toLocaleString();
            document.getElementById('total-luaran').textContent = data.total_luaran_approved.current.toLocaleString();
            document.getElementById('total-peer-review').textContent = data.total_peer_review_approved.current.toLocaleString();
            
            // Update chart data
            progressChart.data.labels = data.progress_penilaian.labels;
            progressChart.data.datasets[0].data = data.progress_penilaian.data;
            progressChart.update();
            
            // Update total pendaftar display
            const totalElement = document.querySelector('.badge.bg-success small');
            if (totalElement) {
                totalElement.textContent = `Total: ${data.progress_penilaian.current_total} Pendaftar`;
            }
        })
        .catch(error => console.error('Error updating dashboard:', error));
}, 30000);


</script>
</x-app-layout> 
