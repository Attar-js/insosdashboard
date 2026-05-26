<x-app-layout :assets="$assets ?? []">
   <div class="row">
      <div class="col-md-12 col-lg-12">
         <div class="row row-cols-1">
            <!-- Statistik Utama -->
            <div class="col-md-3">
               <div class="card card-slide" data-aos="fade-up" data-aos-delay="700">
                  <div class="card-body">
                     <div class="progress-widget">
                        <div id="circle-progress-01" class="circle-progress-01 circle-progress circle-progress-primary text-center" 
                             data-min-value="0" data-max-value="100" data-value="{{ round($nilaiRataRata, 1) }}" data-type="percent">
                           <svg class="card-slie-arrow" width="24" height="24px" viewBox="0 0 24 24">
                              <path fill="currentColor" d="M5,17.59L15.59,7H9V5H19V15H17V8.41L6.41,19L5,17.59Z" />
                           </svg>
                        </div>
                        <div class="progress-detail">
                           <p class="mb-2">Nilai Rata-rata</p>
                           <h4 class="counter">{{ number_format($nilaiRataRata, 1) }}</h4>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            
            <div class="col-md-3">
               <div class="card card-slide" data-aos="fade-up" data-aos-delay="800">
                  <div class="card-body">
                     <div class="progress-widget">
                        <div id="circle-progress-02" class="circle-progress-01 circle-progress circle-progress-info text-center" 
                             data-min-value="0" data-max-value="100" data-value="{{ round($nilaiTertinggi, 1) }}" data-type="percent">
                           <svg class="card-slie-arrow" width="24" height="24" viewBox="0 0 24 24">
                              <path fill="currentColor" d="M19,6.41L17.59,5L7,15.59V9H5V19H15V17H8.41L19,6.41Z" />
                           </svg>
                        </div>
                        <div class="progress-detail">
                           <p class="mb-2">Nilai Tertinggi</p>
                           <h4 class="counter">{{ number_format($nilaiTertinggi, 1) }}</h4>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            
            <div class="col-md-3">
               <div class="card card-slide" data-aos="fade-up" data-aos-delay="900">
                  <div class="card-body">
                     <div class="progress-widget">
                        <div id="circle-progress-03" class="circle-progress-01 circle-progress circle-progress-success text-center" 
                             data-min-value="0" data-max-value="100" data-value="{{ round($nilaiTerendah, 1) }}" data-type="percent">
                           <svg class="card-slie-arrow" width="24" viewBox="0 0 24 24">
                              <path fill="currentColor" d="M19,6.41L17.59,5L7,15.59V9H5V19H15V17H8.41L19,6.41Z" />
                           </svg>
                        </div>
                        <div class="progress-detail">
                           <p class="mb-2">Nilai Terendah</p>
                           <h4 class="counter">{{ number_format($nilaiTerendah, 1) }}</h4>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            
            <div class="col-md-3">
               <div class="card card-slide" data-aos="fade-up" data-aos-delay="1000">
                  <div class="card-body">
                     <div class="progress-widget">
                        <div id="circle-progress-04" class="circle-progress-01 circle-progress circle-progress-warning text-center" 
                             data-min-value="0" data-max-value="100" data-value="{{ $totalMahasiswa }}" data-type="percent">
                           <svg class="card-slie-arrow" width="24px" height="24px" viewBox="0 0 24 24">
                              <path fill="currentColor" d="M5,17.59L15.59,7H9V5H19V15H17V8.41L6.41,19L5,17.59Z" />
                           </svg>
                        </div>
                        <div class="progress-detail">
                           <p class="mb-2">Total Mahasiswa</p>
                           <h4 class="counter">{{ $totalMahasiswa }}</h4>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>

   <div class="row">
      <!-- Distribusi Nilai -->
      <div class="col-lg-6">
         <div class="card" data-aos="fade-up" data-aos-delay="1100">
            <div class="card-header">
               <h5 class="card-title">
                  <i class="fa fa-chart-pie me-2"></i>
                  Distribusi Nilai
               </h5>
            </div>
            <div class="card-body">
               <div class="row">
                  <div class="col-md-6">
                     <div class="d-flex align-items-center mb-3">
                        <div class="bg-success rounded-circle p-2 me-3">
                           <i class="fa fa-star text-white"></i>
                        </div>
                        <div>
                           <h6 class="mb-1">Nilai A (85-100)</h6>
                           <span class="badge bg-success">{{ $distribusiNilai['A'] }} mahasiswa</span>
                        </div>
                     </div>
                  </div>
                  <div class="col-md-6">
                     <div class="d-flex align-items-center mb-3">
                        <div class="bg-info rounded-circle p-2 me-3">
                           <i class="fa fa-star text-white"></i>
                        </div>
                        <div>
                           <h6 class="mb-1">Nilai B (75-84)</h6>
                           <span class="badge bg-info">{{ $distribusiNilai['B'] }} mahasiswa</span>
                        </div>
                     </div>
                  </div>
                  <div class="col-md-6">
                     <div class="d-flex align-items-center mb-3">
                        <div class="bg-warning rounded-circle p-2 me-3">
                           <i class="fa fa-star text-white"></i>
                        </div>
                        <div>
                           <h6 class="mb-1">Nilai C (65-74)</h6>
                           <span class="badge bg-warning">{{ $distribusiNilai['C'] }} mahasiswa</span>
                        </div>
                     </div>
                  </div>
                  <div class="col-md-6">
                     <div class="d-flex align-items-center mb-3">
                        <div class="bg-danger rounded-circle p-2 me-3">
                           <i class="fa fa-star text-white"></i>
                        </div>
                        <div>
                           <h6 class="mb-1">Nilai D & E (<65)</h6>
                           <span class="badge bg-danger">{{ $distribusiNilai['D'] + $distribusiNilai['E'] }} mahasiswa</span>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>

      <!-- Rata-rata Komponen -->
      <div class="col-lg-6">
         <div class="card" data-aos="fade-up" data-aos-delay="1200">
            <div class="card-header">
               <h5 class="card-title">
                  <i class="fa fa-chart-bar me-2"></i>
                  Rata-rata Komponen Penilaian
               </h5>
            </div>
            <div class="card-body">
               <div class="mb-3">
                  <div class="d-flex justify-content-between mb-1">
                     <span>Proposal Kegiatan</span>
                     <span class="fw-bold">{{ number_format($rataRataKomponen['proposal'], 1) }}</span>
                  </div>
                  <div class="progress" style="height: 8px;">
                     <div class="progress-bar bg-primary" style="width: {{ $rataRataKomponen['proposal'] }}%"></div>
                  </div>
               </div>
               
               <div class="mb-3">
                  <div class="d-flex justify-content-between mb-1">
                     <span>Peer Review</span>
                     <span class="fw-bold">{{ number_format($rataRataKomponen['peer_review'], 1) }}</span>
                  </div>
                  <div class="progress" style="height: 8px;">
                     <div class="progress-bar bg-info" style="width: {{ $rataRataKomponen['peer_review'] }}%"></div>
                  </div>
               </div>
               
               <div class="mb-3">
                  <div class="d-flex justify-content-between mb-1">
                     <span>Laporan Akhir</span>
                     <span class="fw-bold">{{ number_format($rataRataKomponen['laporan_akhir'], 1) }}</span>
                  </div>
                  <div class="progress" style="height: 8px;">
                     <div class="progress-bar bg-success" style="width: {{ $rataRataKomponen['laporan_akhir'] }}%"></div>
                  </div>
               </div>
               
               <div class="mb-3">
                  <div class="d-flex justify-content-between mb-1">
                     <span>Presentasi Akhir</span>
                     <span class="fw-bold">{{ number_format($rataRataKomponen['presentasi_akhir'], 1) }}</span>
                  </div>
                  <div class="progress" style="height: 8px;">
                     <div class="progress-bar bg-warning" style="width: {{ $rataRataKomponen['presentasi_akhir'] }}%"></div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>

   <div class="row">
      <!-- Top 5 Mahasiswa -->
      <div class="col-lg-8">
         <div class="card" data-aos="fade-up" data-aos-delay="1300">
            <div class="card-header">
               <h5 class="card-title">
                  <i class="fa fa-trophy me-2"></i>
                  Top 5 Mahasiswa dengan Nilai Tertinggi
               </h5>
            </div>
            <div class="card-body">
               <div class="table-responsive">
                  <table class="table table-hover">
                     <thead>
                        <tr>
                           <th>Rank</th>
                           <th>Nama Mahasiswa</th>
                           <th>NIM</th>
                           <th>Dosen Pembimbing</th>
                           <th>Nilai Akhir</th>
                           <th>Status</th>
                        </tr>
                     </thead>
                     <tbody>
                        @forelse($topMahasiswa as $index => $mahasiswa)
                           <tr>
                              <td>
                                 @if($index == 0)
                                    <span class="badge bg-warning">🥇</span>
                                 @elseif($index == 1)
                                    <span class="badge bg-secondary">🥈</span>
                                 @elseif($index == 2)
                                    <span class="badge bg-danger">🥉</span>
                                 @else
                                    <span class="badge bg-light text-dark">{{ $index + 1 }}</span>
                                 @endif
                              </td>
                              <td>
                                 <div class="d-flex align-items-center">
                                    <div class="bg-primary bg-opacity-10 p-2 rounded me-2">
                                       <i class="fa fa-user text-primary"></i>
                                    </div>
                                    <span class="fw-medium">{{ $mahasiswa->mahasiswa_name }}</span>
                                 </div>
                              </td>
                              <td>{{ $mahasiswa->nim }}</td>
                              <td>{{ $mahasiswa->dosen_name }}</td>
                              <td>
                                 <span class="badge bg-success fs-6">{{ number_format($mahasiswa->nilai_akhir, 1) }}</span>
                              </td>
                              <td>
                                 @if($mahasiswa->nilai_akhir >= 85)
                                    <span class="badge bg-success">A</span>
                                 @elseif($mahasiswa->nilai_akhir >= 75)
                                    <span class="badge bg-info">B</span>
                                 @elseif($mahasiswa->nilai_akhir >= 65)
                                    <span class="badge bg-warning">C</span>
                                 @else
                                    <span class="badge bg-danger">D/E</span>
                                 @endif
                              </td>
                           </tr>
                        @empty
                           <tr>
                              <td colspan="6" class="text-center py-4">
                                 <div class="text-muted">
                                    <i class="fa fa-inbox fa-2x mb-2"></i>
                                    <p>Belum ada data nilai mahasiswa</p>
                                 </div>
                              </td>
                           </tr>
                        @endforelse
                     </tbody>
                  </table>
               </div>
            </div>
         </div>
      </div>

      <!-- Grafik Distribusi -->
      <div class="col-lg-4">
         <div class="card" data-aos="fade-up" data-aos-delay="1400">
            <div class="card-header">
               <h5 class="card-title">
                  <i class="fa fa-chart-pie me-2"></i>
                  Grafik Distribusi
               </h5>
            </div>
            <div class="card-body">
               <canvas id="distribusiChart" width="400" height="400"></canvas>
            </div>
         </div>
      </div>
   </div>

   <!-- Tabel Semua Nilai -->
   <div class="row">
      <div class="col-12">
         <div class="card" data-aos="fade-up" data-aos-delay="1500">
            <div class="card-header">
               <h5 class="card-title">
                  <i class="fa fa-table me-2"></i>
                  Semua Nilai Mahasiswa
               </h5>
            </div>
            <div class="card-body">
               <div class="table-responsive">
                  <table class="table table-hover" id="nilaiTable">
                     <thead>
                        <tr>
                           <th>No</th>
                           <th>Nama Mahasiswa</th>
                           <th>NIM</th>
                           <th>Dosen Pembimbing</th>
                           <th>Proposal</th>
                           <th>Peer Review</th>
                           <th>Laporan Akhir</th>
                           <th>Presentasi</th>
                           <th>Nilai Akhir</th>
                           <th>Status</th>
                           <th>Tanggal</th>
                        </tr>
                     </thead>
                     <tbody>
                        @forelse($nilaiMahasiswa as $index => $mahasiswa)
                           <tr>
                              <td>{{ $index + 1 }}</td>
                              <td>
                                 <div class="d-flex align-items-center">
                                    <div class="bg-primary bg-opacity-10 p-2 rounded me-2">
                                       <i class="fa fa-user text-primary"></i>
                                    </div>
                                    <span class="fw-medium">{{ $mahasiswa->mahasiswa_name }}</span>
                                 </div>
                              </td>
                              <td>{{ $mahasiswa->nim }}</td>
                              <td>{{ $mahasiswa->dosen_name }}</td>
                              <td>{{ number_format($mahasiswa->proposal_kegiatan, 1) }}</td>
                              <td>{{ number_format($mahasiswa->peer_review, 1) }}</td>
                              <td>{{ number_format($mahasiswa->laporan_akhir, 1) }}</td>
                              <td>{{ number_format($mahasiswa->presentasi_akhir, 1) }}</td>
                              <td>
                                 <span class="badge bg-success fs-6">{{ number_format($mahasiswa->nilai_akhir, 1) }}</span>
                              </td>
                              <td>
                                 @if($mahasiswa->nilai_akhir >= 85)
                                    <span class="badge bg-success">A</span>
                                 @elseif($mahasiswa->nilai_akhir >= 75)
                                    <span class="badge bg-info">B</span>
                                 @elseif($mahasiswa->nilai_akhir >= 65)
                                    <span class="badge bg-warning">C</span>
                                 @else
                                    <span class="badge bg-danger">D/E</span>
                                 @endif
                              </td>
                              <td>{{ \Carbon\Carbon::parse($mahasiswa->tanggal_penilaian)->format('d/m/Y') }}</td>
                           </tr>
                        @empty
                           <tr>
                              <td colspan="11" class="text-center py-4">
                                 <div class="text-muted">
                                    <i class="fa fa-inbox fa-2x mb-2"></i>
                                    <p>Belum ada data nilai mahasiswa</p>
                                 </div>
                              </td>
                           </tr>
                        @endforelse
                     </tbody>
                  </table>
               </div>
            </div>
         </div>
      </div>
   </div>
</x-app-layout>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
   $(document).ready(function() {
      // Initialize DataTable
      $('#nilaiTable').DataTable({
         "pageLength": 10,
         "order": [[8, "desc"]], // Sort by nilai akhir
         "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json"
         }
      });

      // Chart.js untuk distribusi nilai
      const ctx = document.getElementById('distribusiChart').getContext('2d');
      new Chart(ctx, {
         type: 'doughnut',
         data: {
            labels: ['A (85-100)', 'B (75-84)', 'C (65-74)', 'D/E (<65)'],
            datasets: [{
               data: [
                  {{ $distribusiNilai['A'] }},
                  {{ $distribusiNilai['B'] }},
                  {{ $distribusiNilai['C'] }},
                  {{ $distribusiNilai['D'] + $distribusiNilai['E'] }}
               ],
               backgroundColor: [
                  '#28a745',
                  '#17a2b8',
                  '#ffc107',
                  '#dc3545'
               ],
               borderWidth: 2,
               borderColor: '#fff'
            }]
         },
         options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
               legend: {
                  position: 'bottom'
               }
            }
         }
      });
   });
</script>
@endpush 
