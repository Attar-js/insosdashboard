<x-app-layout :assets="$assets ?? []">
@section('title') Edit Nilai Capaian CPMK @endsection
@section('css')
<link rel="stylesheet" type="text/css" href="{{asset('assets/css/vendors/dropzone.css')}}">
@endsection
@section('content')

<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Edit Nilai Capaian CPMK</h4>
                        <div class="card-tools">
                            <a href="{{ route('nilai-cpmk.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kembali
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

                    <form action="{{ route('nilai-cpmk.update', $nilaiCpmk->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="mahasiswa_id" class="form-label">Pilih Mahasiswa <span class="text-danger">*</span></label>
                                    <select class="form-select @error('mahasiswa_id') is-invalid @enderror" 
                                            id="mahasiswa_id" name="mahasiswa_id" required>
                                        <option value="">Pilih Mahasiswa</option>
                                        @foreach($mahasiswaList ?? [] as $mahasiswa)
                                            <option value="{{ $mahasiswa['id'] }}" 
                                                    data-nim="{{ $mahasiswa['nim'] }}"
                                                    data-name="{{ $mahasiswa['name'] }}"
                                                    data-email="{{ $mahasiswa['email'] }}"
                                                    {{ old('mahasiswa_id', $currentMahasiswaId) == $mahasiswa['id'] ? 'selected' : '' }}>
                                                {{ $mahasiswa['nim'] }} - {{ $mahasiswa['name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('mahasiswa_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="judul_kegiatan" class="form-label">Judul Kegiatan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('judul_kegiatan') is-invalid @enderror" 
                                   id="judul_kegiatan" name="judul_kegiatan" 
                                   value="{{ old('judul_kegiatan', $nilaiCpmk->judul_kegiatan) }}" required>
                            @error('judul_kegiatan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                                                 <div class="mb-3">
                             <label for="catatan" class="form-label">Catatan</label>
                             <textarea class="form-control @error('catatan') is-invalid @enderror" 
                                       id="catatan" name="catatan" rows="3">{{ old('catatan', $nilaiCpmk->catatan) }}</textarea>
                             @error('catatan')
                                 <div class="invalid-feedback">{{ $message }}</div>
                             @enderror
                         </div>

                         

                        <div class="mb-3">
                            <label class="form-label">File Saat Ini</label>
                            <div class="d-flex align-items-center p-3 bg-light rounded">
                                <i class="fa fa-file-pdf text-danger me-3"></i>
                                <div>
                                    <div class="fw-bold">{{ $nilaiCpmk->file_name }}</div>
                                    <small class="text-muted">{{ $nilaiCpmk->formatted_file_size }}</small>
                                </div>
                                <div class="ms-auto">
                                    <a href="{{ route('nilai-cpmk.download', $nilaiCpmk->id) }}" 
                                       class="btn btn-sm btn-outline-success">
                                        <i class="fa fa-download"></i> Download
                                    </a>
                                </div>
                            </div>
                        </div>

                                                 <div class="mb-3">
                             <label for="file_pdf" class="form-label">Upload File Baru (Opsional)</label>
                             <input type="file" class="form-control @error('file_pdf') is-invalid @enderror" 
                                    id="file_pdf" name="file_pdf" accept=".pdf">
                             <small class="form-text text-muted">Biarkan kosong jika tidak ingin mengubah file. Hanya file PDF yang diperbolehkan.</small>
                             @error('file_pdf')
                                 <div class="invalid-feedback">{{ $message }}</div>
                             @enderror
                         </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('nilai-cpmk.index') }}" class="btn btn-secondary">
                                <i class="fa fa-times me-1"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save me-1"></i> Update Nilai Capaian CPMK
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</x-app-layout>

@section('script')
<script>
    // Preview file yang dipilih
    document.getElementById('file_pdf').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            if (file.type !== 'application/pdf') {
                alert('Hanya file PDF yang diperbolehkan!');
                this.value = '';
                return;
            }
            
            if (file.size > 10 * 1024 * 1024) { // 10MB
                alert('Ukuran file maksimal 10MB!');
                this.value = '';
                return;
            }
        }
    });

</script>
@endsection 
