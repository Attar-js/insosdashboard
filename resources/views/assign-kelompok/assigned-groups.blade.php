<x-app-layout :assets="$assets ?? []">
<div class="container-fluid">
    <!-- Page Title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Kelompok yang Sudah Diassign</h4>
                <a href="{{ route('assign-kelompok.index') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Assign Kelompok Baru
                </a>
            </div>
        </div>
    </div>

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

    <!-- Stats Cards -->
    <div class="row">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h4 class="mb-0">{{ $assignments->count() }}</h4>
                            <p class="mb-0">Total Assignment</p>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h4 class="mb-0">{{ $assignments->where('status', 'assigned')->count() }}</h4>
                            <p class="mb-0">Assigned</p>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h4 class="mb-0">{{ $assignments->where('status', 'pending')->count() }}</h4>
                            <p class="mb-0">Pending</p>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h4 class="mb-0">{{ $assignments->unique('dosen_id')->count() }}</h4>
                            <p class="mb-0">Dosen Aktif</p>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="fas fa-chalkboard-teacher fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Assignments Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list me-2"></i>Daftar Assignment
                    </h5>
                </div>
                <div class="card-body">
                    @if($assignments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-primary">
                                    <tr>
                                        <th scope="col" width="5%">No</th>
                                        <th scope="col" width="15%">Nama Kelompok</th>
                                        <th scope="col" width="20%">Judul Kegiatan</th>
                                        <th scope="col" width="15%">Dosen Pembimbing</th>
                                        <th scope="col" width="10%">Status</th>
                                        <th scope="col" width="12%">Tanggal Assign</th>
                                        <th scope="col" width="10%">Assigned By</th>
                                        <th scope="col" width="13%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($assignments as $index => $assignment)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <strong>{{ $assignment->group_name }}</strong>
                                                <br>
                                                <small class="text-muted">ID: {{ $assignment->group_id }}</small>
                                            </td>
                                            <td>{{ $assignment->judul_kegiatan }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm me-2">
                                                        <div class="avatar-title bg-primary rounded-circle">
                                                            {{ strtoupper(substr($assignment->dosen_name, 0, 1)) }}
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <strong>{{ $assignment->dosen_name }}</strong>
                                                        <br>
                                                        <small class="text-muted">ID: {{ $assignment->dosen_id }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @if($assignment->status == 'assigned')
                                                    <span class="badge bg-success">Assigned</span>
                                                @elseif($assignment->status == 'pending')
                                                    <span class="badge bg-warning">Pending</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ ucfirst($assignment->status) }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $assignment->assigned_at ? $assignment->assigned_at->format('d/m/Y H:i') : 'N/A' }}
                                            </td>
                                            <td>
                                                @if($assignment->assignedBy)
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar avatar-sm me-2">
                                                            <div class="avatar-title bg-info rounded-circle">
                                                                {{ strtoupper(substr($assignment->assignedBy->name, 0, 1)) }}
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <small>{{ $assignment->assignedBy->name }}</small>
                                                        </div>
                                                    </div>
                                                @else
                                                    <small class="text-muted">System</small>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-info btn-sm" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#detailModal{{ $assignment->id }}">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-warning btn-sm" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#editModal{{ $assignment->id }}">
                                                        <i class="fas fa-edit"></i>
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
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Belum ada assignment</h5>
                            <p class="text-muted">Silakan assign kelompok terlebih dahulu</p>
                            <a href="{{ route('assign-kelompok.index') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>Assign Kelompok
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Detail Modals -->
@foreach($assignments as $assignment)
<div class="modal fade" id="detailModal{{ $assignment->id }}" tabindex="-1" aria-labelledby="detailModalLabel{{ $assignment->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailModalLabel{{ $assignment->id }}">
                    Detail Assignment - {{ $assignment->group_name }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Informasi Kelompok:</h6>
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Nama Kelompok:</strong></td>
                                <td>{{ $assignment->group_name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Judul Kegiatan:</strong></td>
                                <td>{{ $assignment->judul_kegiatan }}</td>
                            </tr>
                            <tr>
                                <td><strong>Nama Mitra:</strong></td>
                                <td>{{ $assignment->nama_mitra ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Lokasi Mitra:</strong></td>
                                <td>{{ $assignment->lokasi_mitra ?? 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Informasi Assignment:</h6>
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Dosen Pembimbing:</strong></td>
                                <td>{{ $assignment->dosen_name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td>
                                    @if($assignment->status == 'assigned')
                                        <span class="badge bg-success">Assigned</span>
                                    @elseif($assignment->status == 'pending')
                                        <span class="badge bg-warning">Pending</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($assignment->status) }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Tanggal Assign:</strong></td>
                                <td>{{ $assignment->assigned_at ? $assignment->assigned_at->format('d/m/Y H:i') : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Assigned By:</strong></td>
                                <td>{{ $assignment->assignedBy ? $assignment->assignedBy->name : 'System' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                @if($assignment->assignment_note)
                <div class="row mt-3">
                    <div class="col-12">
                        <h6>Catatan Assignment:</h6>
                        <div class="alert alert-info">
                            {{ $assignment->assignment_note }}
                        </div>
                    </div>
                </div>
                @endif
                
                @if($assignment->group_members)
                <div class="row mt-3">
                    <div class="col-12">
                        <h6>Anggota Kelompok:</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama</th>
                                        <th>NIM</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach(json_decode($assignment->group_members, true) as $index => $member)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $member['name'] }}</td>
                                            <td>{{ $member['nim'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endforeach

<script>
$(document).ready(function() {
    // Auto hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});
</script>
</x-app-layout> 
