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
                        <i class="fas fa-plus me-2"></i>Tambah Penilaian Baru
                    </h4>
                    <p class="text-muted mb-0">Input penilaian mahasiswa berdasarkan sistem CPMK</p>
                </div>
                <div>
                    <a href="{{ route('penilaian.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-edit me-2"></i>Form Penilaian Mahasiswa
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('penilaian.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="mahasiswa_nim" class="form-label">Pilih Mahasiswa <span class="text-danger">*</span></label>
                                    <select class="form-select @error('mahasiswa_nim') is-invalid @enderror" 
                                            id="mahasiswa_nim" 
                                            name="mahasiswa_nim" 
                                            required>
                                        <option value="">Pilih Mahasiswa</option>
                                        @foreach($mahasiswa as $mhs)
                                            <option value="{{ $mhs->nim }}" {{ old('mahasiswa_nim') == $mhs->nim ? 'selected' : '' }}>
                                                {{ $mhs->first_name }} {{ $mhs->last_name }} ({{ $mhs->nim }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('mahasiswa_nim')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="tanggal_penilaian" class="form-label">Tanggal Penilaian</label>
                                    <input type="date" 
                                           class="form-control @error('tanggal_penilaian') is-invalid @enderror" 
                                           id="tanggal_penilaian" 
                                           name="tanggal_penilaian" 
                                           value="{{ old('tanggal_penilaian', date('Y-m-d')) }}">
                                    @error('tanggal_penilaian')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nilai_kehadiran" class="form-label">
                                        CPMK1 - Pemecahan Masalah (35%) <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" 
                                           class="form-control @error('nilai_kehadiran') is-invalid @enderror" 
                                           id="nilai_kehadiran" 
                                           name="nilai_kehadiran" 
                                           min="0" 
                                           max="100" 
                                           step="0.1" 
                                           value="{{ old('nilai_kehadiran') }}" 
                                           required>
                                    <small class="text-muted">Pemahaman terhadap permasalahan mitra, keterlibatan dalam perumusan program</small>
                                    @error('nilai_kehadiran')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nilai_tugas" class="form-label">
                                        CPMK2 - Kolaborasi dan Implementasi IPTEK (30%) <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" 
                                           class="form-control @error('nilai_tugas') is-invalid @enderror" 
                                           id="nilai_tugas" 
                                           name="nilai_tugas" 
                                           min="0" 
                                           max="100" 
                                           step="0.1" 
                                           value="{{ old('nilai_tugas') }}" 
                                           required>
                                    <small class="text-muted">Peran aktif dalam kerja tim, penerapan ilmu sesuai bidang studi</small>
                                    @error('nilai_tugas')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nilai_praktikum" class="form-label">
                                        CPMK3 - Pelaporan Hasil Kegiatan (35%) <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" 
                                           class="form-control @error('nilai_praktikum') is-invalid @enderror" 
                                           id="nilai_praktikum" 
                                           name="nilai_praktikum" 
                                           min="0" 
                                           max="100" 
                                           step="0.1" 
                                           value="{{ old('nilai_praktikum') }}" 
                                           required>
                                    <small class="text-muted">Kontribusi dalam penyusunan laporan akhir, kualitas luaran</small>
                                    @error('nilai_praktikum')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nilai_akhir" class="form-label">Nilai Akhir <span class="text-danger">*</span></label>
                                    <input type="number" 
                                           class="form-control @error('nilai_akhir') is-invalid @enderror" 
                                           id="nilai_akhir" 
                                           name="nilai_akhir" 
                                           min="0" 
                                           max="100" 
                                           step="0.1" 
                                           value="{{ old('nilai_akhir') }}" 
                                           readonly>
                                    <small class="text-muted">Nilai akhir akan dihitung otomatis berdasarkan CPMK</small>
                                    @error('nilai_akhir')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="file_nilai" class="form-label">Upload File Penilaian (PDF)</label>
                                    <input type="file" 
                                           class="form-control @error('file_nilai') is-invalid @enderror" 
                                           id="file_nilai" 
                                           name="file_nilai" 
                                           accept=".pdf">
                                    <small class="text-muted">Upload file PDF penilaian (maksimal 2MB)</small>
                                    @error('file_nilai')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="catatan" class="form-label">Catatan</label>
                                    <textarea class="form-control @error('catatan') is-invalid @enderror" 
                                              id="catatan" 
                                              name="catatan" 
                                              rows="3" 
                                              placeholder="Tambahkan catatan penilaian...">{{ old('catatan') }}</textarea>
                                    @error('catatan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('penilaian.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Kembali
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Simpan Penilaian
                            </button>
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
    
    // Auto calculate nilai akhir
    function calculateNilaiAkhir() {
        const nilaiKehadiran = parseFloat($('#nilai_kehadiran').val()) || 0;
        const nilaiTugas = parseFloat($('#nilai_tugas').val()) || 0;
        const nilaiPraktikum = parseFloat($('#nilai_praktikum').val()) || 0;
        
        // Calculate based on CPMK weights
        const nilaiAkhir = (nilaiKehadiran * 0.35) + (nilaiTugas * 0.30) + (nilaiPraktikum * 0.35);
        
        $('#nilai_akhir').val(nilaiAkhir.toFixed(1));
    }
    
    // Calculate on input change
    $('#nilai_kehadiran, #nilai_tugas, #nilai_praktikum').on('input', calculateNilaiAkhir);
    
    // Initial calculation
    calculateNilaiAkhir();
});
</script>
</x-app-layout> 
