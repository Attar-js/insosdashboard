<x-app-layout :assets="$assets ?? []">
<div class="container-fluid">
    <!-- Notifications -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">
                        <i class="fas fa-chart-line me-2"></i>Manajemen Penilaian Mahasiswa
                    </h4>
                    <p class="text-muted mb-0">Kelola penilaian mahasiswa berdasarkan sistem CPMK</p>
                </div>
                <div>
                    <a href="{{ route('penilaian.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Tambah Penilaian
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Penilaian Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-list me-2"></i>Daftar Penilaian
                        </h5>
                        <div class="card-tools">
                            <span class="badge bg-primary fs-6">{{ $penilaian->count() }} Penilaian</span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if($penilaian && $penilaian->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-primary">
                                    <tr>
                                        <th scope="col" width="5%">No</th>
                                        <th scope="col" width="20%">Mahasiswa</th>
                                        <th scope="col" width="10%">NIM</th>
                                        <th scope="col" width="10%">Nilai Akhir</th>
                                        <th scope="col" width="10%">Status</th>
                                        <th scope="col" width="15%">Tanggal</th>
                                        <th scope="col" width="10%">File</th>
                                        <th scope="col" width="15%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($penilaian as $index => $nilai)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <h6 class="mb-0">{{ $nilai->mahasiswa_nama }}</h6>
                                                <small class="text-muted">Dosen: {{ $nilai->dosen_nama }}</small>
                                            </td>
                                            <td>
                                                <code class="text-primary">{{ $nilai->mahasiswa_nim }}</code>
                                            </td>
                                            <td class="text-center">
                                                @php
                                                    $badgeClass = '';
                                                    if ($nilai->nilai_akhir >= 85) {
                                                        $badgeClass = 'bg-success';
                                                    } elseif ($nilai->nilai_akhir >= 75) {
                                                        $badgeClass = 'bg-primary';
                                                    } elseif ($nilai->nilai_akhir >= 60) {
                                                        $badgeClass = 'bg-warning';
                                                    } else {
                                                        $badgeClass = 'bg-danger';
                                                    }
                                                @endphp
                                                <span class="badge {{ $badgeClass }} fs-6">{{ number_format($nilai->nilai_akhir, 1) }}</span>
                                            </td>
                                            <td class="text-center">
                                                @if($nilai->status == 'approved')
                                                    <span class="badge bg-success">Disetujui</span>
                                                @elseif($nilai->status == 'rejected')
                                                    <span class="badge bg-danger">Ditolak</span>
                                                @else
                                                    <span class="badge bg-warning">Pending</span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ \Carbon\Carbon::parse($nilai->tanggal_penilaian)->format('d/m/Y') }}
                                            </td>
                                            <td class="text-center">
                                                @if($nilai->file_nilai)
                                                    <a href="{{ route('penilaian.download', $nilai->file_nilai) }}" 
                                                       class="btn btn-outline-info btn-sm" 
                                                       title="Download File">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('penilaian.show', $nilai->id) }}" 
                                                       class="btn btn-info btn-sm" 
                                                       title="Lihat Detail">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('penilaian.edit', $nilai->id) }}" 
                                                       class="btn btn-warning btn-sm" 
                                                       title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-danger btn-sm" 
                                                            onclick="confirmDelete('{{ $nilai->id }}', '{{ $nilai->mahasiswa_nama }}')"
                                                            title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="fas fa-chart-line fa-4x text-muted"></i>
                            </div>
                            <h5 class="text-muted mb-3">Belum ada data penilaian</h5>
                            <p class="text-muted mb-4">Data penilaian akan muncul setelah dosen melakukan penilaian terhadap mahasiswa</p>
                            <a href="{{ route('penilaian.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Tambah Penilaian Pertama
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Form (Hidden) -->
<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
$(document).ready(function() {
    // Auto hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});

function confirmDelete(id, nama) {
    if (confirm('Apakah Anda yakin ingin menghapus penilaian untuk "' + nama + '"?')) {
        var form = document.getElementById('deleteForm');
        form.action = '{{ route("penilaian.destroy", "") }}/' + id;
        form.submit();
    }
}
</script>
</x-app-layout> 
