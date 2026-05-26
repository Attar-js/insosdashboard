<x-app-layout :assets="$assets ?? []">
@section('title') Nilai Capaian CPMK @endsection
@section('css')
<link rel="stylesheet" type="text/css" href="{{asset('assets/css/vendors/datatables.css')}}">
@endsection
@section('content')

<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Nilai Capaian CPMK</h4>
                        <div class="card-tools">
                            <a href="{{ route('nilai-cpmk.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Tambah Nilai Capaian CPMK
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fa fa-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fa fa-exclamation-triangle me-2"></i>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="nilaiCpmkTable">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>NIM Mahasiswa</th>
                                    <th>Judul Penilaian</th>
                                    <th>File</th>
                                    <th>Uploaded By</th>
                                    <th>Tanggal Upload</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($nilaiCpmk as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            @if(!empty(trim($item->nim_mahasiswa ?? '')))
                                                <span class="fw-bold">{{ $item->nim_mahasiswa }}</span>
                                            @else
                                                <span class="badge bg-secondary">Belum diassign</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 200px;" title="{{ $item->judul_kegiatan }}">
                                                {{ $item->judul_kegiatan }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="fa fa-file-pdf text-danger me-2"></i>
                                                <div>
                                                    <div class="fw-bold">{{ $item->file_name }}</div>
                                                    <small class="text-muted">{{ $item->formatted_file_size }}</small>
                                                </div>
                                            </div>
                                        </td>

                                        <td>
                                            <span class="badge bg-info">{{ $item->uploaded_by }}</span>
                                        </td>
                                        <td>
                                            <div class="text-muted">
                                                <div>{{ $item->uploaded_at->format('d/m/Y') }}</div>
                                                <small>{{ $item->uploaded_at->format('H:i') }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1 justify-content-center">
                                                <!-- Tombol Edit -->
                                                <a href="{{ route('nilai-cpmk.edit', $item->id) }}" 
                                                   class="btn btn-sm btn-warning" 
                                                   title="Edit Nilai Capaian CPMK"
                                                   data-bs-toggle="tooltip">
                                                    <i class="fa fa-edit me-1"></i>
                                                    <span class="d-none d-sm-inline">Edit</span>
                                                </a>
                                                
                                                <!-- Tombol Download -->
                                                <a href="{{ route('nilai-cpmk.download', $item->id) }}" 
                                                   class="btn btn-sm btn-success" 
                                                   title="Download PDF"
                                                   data-bs-toggle="tooltip">
                                                    <i class="fa fa-download me-1"></i>
                                                    <span class="d-none d-sm-inline">Download</span>
                                                </a>
                                                
                                                <!-- Tombol Preview -->
                                                <a href="{{ route('nilai-cpmk.view', $item->id) }}" 
                                                   class="btn btn-sm btn-info" 
                                                   title="Preview PDF" 
                                                   target="_blank"
                                                   data-bs-toggle="tooltip">
                                                    <i class="fa fa-eye me-1"></i>
                                                    <span class="d-none d-sm-inline">Preview</span>
                                                </a>
                                                
                                                @if(!empty(trim($item->nim_mahasiswa ?? '')))
                                                    <!-- Tombol Unassign -->
                                                    <form action="{{ route('nilai-cpmk.unassign', $item->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus assignment nilai CPMK ini? Data tidak akan dihapus, hanya assignment yang dihapus.')">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-warning" title="Hapus Assignment" data-bs-toggle="tooltip">
                                                            <i class="fa fa-user-times me-1"></i>
                                                            <span class="d-none d-sm-inline">Unassign</span>
                                                        </button>
                                                    </form>
                                                @else
                                                    <!-- Tombol Assign (ketika belum diassign) -->
                                                    <a href="{{ route('nilai-cpmk.edit', $item->id) }}" class="btn btn-sm btn-primary" title="Assign ke Mahasiswa" data-bs-toggle="tooltip">
                                                        <i class="fa fa-user-plus me-1"></i>
                                                        <span class="d-none d-sm-inline">Assign</span>
                                                    </a>
                                                @endif
                                                
                                                <!-- Tombol Hapus -->
                                                <form action="{{ route('nilai-cpmk.destroy', $item->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data nilai CPMK ini secara permanen? Data yang sudah dihapus tidak dapat dikembalikan.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Hapus Data" data-bs-toggle="tooltip">
                                                        <i class="fa fa-trash me-1"></i>
                                                        <span class="d-none d-sm-inline">Hapus</span>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fa fa-inbox fa-3x mb-3"></i>
                                                <h6>Belum ada file nilai Capaian CPMK</h6>
                                                <p>Upload file nilai Capaian CPMK pertama Anda</p>
                                                <a href="{{ route('nilai-cpmk.create') }}" class="btn btn-primary">
                                                    <i class="fa fa-plus"></i> Upload File
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($nilaiCpmk->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $nilaiCpmk->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>



</x-app-layout>

@section('script')
<script src="{{asset('assets/js/datatable/datatables/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('assets/js/datatable/datatables/datatable.custom.js')}}"></script>
<script>
    $(document).ready(function() {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Initialize DataTable
        $('#nilaiCpmkTable').DataTable({
            "pageLength": 10,
            "order": [[6, "desc"]], // Sort by upload date
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json"
            },
            "columnDefs": [
                { "orderable": false, "targets": [0, 7] } // Disable sorting for No and Action columns
            ]
        });
    });


</script>
@endsection 
