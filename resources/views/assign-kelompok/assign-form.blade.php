<x-app-layout :assets="$assets ?? []">
<div class="container-fluid">
    <!-- Page Title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Assign Dosen ke Kelompok</h4>
                <a href="{{ route('assign-kelompok.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Notifications -->
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Group Information -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>Informasi Kelompok
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td width="30%"><strong>Nama Kelompok:</strong></td>
                                    <td>{{ $group['nama_kelompok'] }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Judul Kegiatan:</strong></td>
                                    <td>{{ $group['judul_kegiatan'] }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Lokasi KKN:</strong></td>
                                    <td>{{ $group['lokasi_kkn'] }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Nama Mitra:</strong></td>
                                    <td>{{ $group['nama_mitra'] ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Lokasi Mitra:</strong></td>
                                    <td>{{ $group['lokasi_mitra'] ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-3">Anggota Kelompok:</h6>
                            @foreach($group['members'] as $index => $member)
                                <div class="d-flex align-items-center mb-2">
                                    <div class="avatar avatar-sm me-3">
                                        <div class="avatar-title bg-primary rounded-circle">
                                            {{ strtoupper(substr($member['name'], 0, 1)) }}
                                        </div>
                                    </div>
                                    <div>
                                        <strong>{{ $member['name'] }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $member['nim'] }}</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Assign Form -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user-plus me-2"></i>Pilih Dosen Pembimbing
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('assign-kelompok.assign-dosen', $group['id']) }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="dosen_id" class="form-label">
                                        <strong>Dosen Pembimbing <span class="text-danger">*</span></strong>
                                    </label>
                                    <select class="form-select @error('dosen_id') is-invalid @enderror" 
                                            id="dosen_id" name="dosen_id" required>
                                        <option value="">Pilih Dosen Pembimbing</option>
                                        @foreach($dosenList as $dosen)
                                            <option value="{{ $dosen['id'] }}" 
                                                    {{ old('dosen_id') == $dosen['id'] ? 'selected' : '' }}>
                                                {{ $dosen['name'] }} - {{ $dosen['nip'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('dosen_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="assignment_note" class="form-label">
                                        <strong>Catatan Assignment</strong>
                                    </label>
                                    <textarea class="form-control @error('assignment_note') is-invalid @enderror" 
                                              id="assignment_note" name="assignment_note" rows="4" 
                                              placeholder="Catatan tambahan untuk assignment ini (opsional)">{{ old('assignment_note') }}</textarea>
                                    @error('assignment_note')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            <i class="fas fa-info-circle me-2"></i>Informasi
                                        </h6>
                                        <ul class="list-unstyled mb-0">
                                            <li class="mb-2">
                                                <small class="text-muted">
                                                    <i class="fas fa-check-circle me-1 text-success"></i>
                                                    Kelompok sudah 100% terverifikasi
                                                </small>
                                            </li>
                                            <li class="mb-2">
                                                <small class="text-muted">
                                                    <i class="fas fa-users me-1 text-primary"></i>
                                                    {{ count($group['members']) }} anggota kelompok
                                                </small>
                                            </li>
                                            <li class="mb-2">
                                                <small class="text-muted">
                                                    <i class="fas fa-calendar me-1 text-info"></i>
                                                    Assignment akan dicatat dengan timestamp
                                                </small>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-end">
                                    <a href="{{ route('assign-kelompok.index') }}" class="btn btn-secondary me-2">
                                        <i class="fas fa-times me-1"></i>Batal
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>Assign Dosen
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Auto hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
    
    // Form validation
    $('#dosen_id').on('change', function() {
        if ($(this).val()) {
            $(this).removeClass('is-invalid').addClass('is-valid');
        } else {
            $(this).removeClass('is-valid').addClass('is-invalid');
        }
    });

});

</script>
</x-app-layout> 
