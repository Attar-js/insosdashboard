<x-app-layout :assets="$assets ?? []">
@section('title') Assign Nilai Capaian CPMK ke Mahasiswa @endsection
@section('css')
<link rel="stylesheet" type="text/css" href="{{asset('assets/css/vendors/datatables.css')}}">
<style>
    .assignment-card {
        border: 2px dashed #dee2e6;
        background: #f8f9fa;
        transition: all 0.3s ease;
    }

    .assignment-card:hover {
        border-color: #007bff;
        background: #f0f8ff;
    }

    .info-card {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
    }

    .file-preview {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 15px;
        margin-top: 10px;
    }
    
    /* Button styling for debugging */
    .btn-primary {
        background-color: #007bff !important;
        border-color: #007bff !important;
        color: white !important;
        opacity: 1 !important;
        cursor: pointer !important;
    }
    
    .btn-secondary {
        background-color: #6c757d !important;
        border-color: #6c757d !important;
        color: white !important;
    }
    
    .btn:disabled {
        opacity: 0.6 !important;
        cursor: not-allowed !important;
    }
    
    .btn:not(:disabled) {
        opacity: 1 !important;
        cursor: pointer !important;
    }
    
    /* Force button to be clickable */
    #assignBtn {
        pointer-events: auto !important;
        cursor: pointer !important;
        opacity: 1 !important;
    }
</style>
@endsection
@section('content')

<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">
                            <i class="fas fa-user-plus me-2"></i>Assign Nilai Capaian CPMK ke Mahasiswa
                        </h4>
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
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
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

                    <!-- Assignment Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <h6 class="alert-heading">
                                    <i class="fas fa-info-circle me-2"></i>Informasi Assignment
                                </h6>
                                <p class="mb-0">
                                    Pilih mahasiswa dan upload file nilai Capaian CPMK. Setiap mahasiswa hanya bisa memiliki satu nilai Capaian CPMK.
                                </p>
                            </div>
                        </div>
                    </div>

                                         <!-- Assignment Form -->
                     <form id="assignmentForm" action="{{ route('nilai-cpmk.store') }}" method="POST" enctype="multipart/form-data">
                         <script>
                             console.log('📝 Form action:', '{{ route('nilai-cpmk.store') }}');
                         </script>
                        @csrf
                        
                        <div class="row">
                            <!-- Pilih Mahasiswa -->
                            <div class="col-md-4">
                                <div class="card assignment-card">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-user-graduate me-2"></i>Pilih Mahasiswa
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="mahasiswa_id" class="form-label">
                                                <strong>Mahasiswa <span class="text-danger">*</span></strong>
                                            </label>
                                            <select class="form-select @error('mahasiswa_id') is-invalid @enderror" 
                                                    id="mahasiswa_id" 
                                                    name="mahasiswa_id" 
                                                    required>
                                                <option value="">Pilih Mahasiswa</option>
                                                @foreach($mahasiswaList ?? [] as $mahasiswa)
                                                    <option value="{{ $mahasiswa['id'] }}" 
                                                            data-nim="{{ $mahasiswa['nim'] }}"
                                                            data-name="{{ $mahasiswa['name'] }}"
                                                            data-email="{{ $mahasiswa['email'] }}">
                                                        {{ $mahasiswa['nim'] }} - {{ $mahasiswa['name'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('mahasiswa_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Informasi Mahasiswa -->
                                        <div class="info-card" id="infoMahasiswa" style="display: none;">
                                            <div class="card-body">
                                                <h6 class="card-title">
                                                    <i class="fas fa-info-circle me-2"></i>Informasi Mahasiswa
                                                </h6>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <table class="table table-sm">
                                                            <tr>
                                                                <td width="40%"><strong>NIM:</strong></td>
                                                                <td id="info-nim">-</td>
                                                            </tr>
                                                            <tr>
                                                                <td><strong>Nama:</strong></td>
                                                                <td id="info-name">-</td>
                                                            </tr>
                                                            <tr>
                                                                <td><strong>Email:</strong></td>
                                                                <td id="info-email">-</td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Judul Penilaian -->
                            <div class="col-md-6">
                                <div class="card assignment-card">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-edit me-2"></i>Judul Penilaian
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="judul_penilaian" class="form-label">
                                                <strong>Judul Penilaian <span class="text-danger">*</span></strong>
                                            </label>
                                            <input type="text" 
                                                   class="form-control @error('judul_penilaian') is-invalid @enderror" 
                                                   id="judul_penilaian" 
                                                   name="judul_penilaian" 
                                                   value="{{ old('judul_penilaian', 'Nilai CPMK KKN') }}"
                                                   placeholder="Masukkan judul penilaian"
                                                   required>
                                            @error('judul_penilaian')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="form-text text-muted">
                                                <i class="fa fa-info-circle"></i> 
                                                Contoh: Nilai Capaian CPMK, Penilaian CPMK Semester 1, dll.
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Upload File -->
                            <div class="col-md-4">
                                <div class="card assignment-card">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-file-upload me-2"></i>Upload File Nilai Capaian CPMK
                                        </h6>
                                    </div>
                                                                         <div class="card-body">
                                         <div class="mb-3">
                                            <label for="file_pdf" class="form-label">
                                                <strong>File PDF Nilai Capaian CPMK <span class="text-danger">*</span></strong>
                                            </label>
                                            <input type="file" 
                                                   class="form-control @error('file_pdf') is-invalid @enderror" 
                                                   id="file_pdf" 
                                                   name="file_pdf" 
                                                   accept=".pdf"
                                                   required>
                                            @error('file_pdf')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="form-text text-muted">
                                                <i class="fa fa-info-circle"></i> 
                                                Format: PDF. Maksimal: 10MB
                                            </small>
                                            
                                            <!-- File Preview -->
                                            <div id="filePreview" class="file-preview" style="display: none;">
                                                <div class="d-flex align-items-center">
                                                    <i class="fa fa-file-pdf text-danger fa-2x me-3"></i>
                                                    <div>
                                                        <h6 id="fileName" class="mb-1"></h6>
                                                        <small id="fileSize" class="text-muted"></small>
                                                    </div>
                                                </div>
                                            </div>
                                                                                 </div>
                                     </div>
                                </div>
                            </div>
                        </div>

                        <!-- Comment Field -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card assignment-card">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-comment me-2"></i>Komentar (Opsional)
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="catatan" class="form-label">
                                                <strong>Komentar untuk Mahasiswa</strong>
                                            </label>
                                            <textarea class="form-control @error('catatan') is-invalid @enderror" 
                                                      id="catatan" 
                                                      name="catatan" 
                                                      rows="4" 
                                                      placeholder="Masukkan komentar atau instruksi khusus untuk mahasiswa (opsional)...">{{ old('catatan') }}</textarea>
                                            @error('catatan')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="form-text text-muted">
                                                <i class="fa fa-info-circle"></i> 
                                                Komentar ini akan terlihat oleh mahasiswa di halaman Nilai CPMK mereka.
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Assignment Info -->
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="card info-card">
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            <i class="fas fa-info-circle me-2"></i>Informasi Assignment
                                        </h6>
                                        <div class="row">
                                            <div class="col-6">
                                                <small class="text-muted">Assigned By:</small>
                                                <div class="fw-bold">{{ auth()->user()->username ?? auth()->user()->nim ?? 'Tim Penciri' }}</div>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted">Tanggal:</small>
                                                <div class="fw-bold">{{ now()->format('d/m/Y H:i') }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card info-card">
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            <i class="fas fa-info-circle me-2"></i>Status Assignment
                                        </h6>
                                        <span class="badge bg-warning">Pending</span>
                                        <small class="text-muted d-block mt-1">
                                            File akan berstatus pending setelah diassign
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('nilai-cpmk.index') }}" class="btn btn-secondary">
                                        <i class="fa fa-times"></i> Batal
                                    </a>
                                    <button type="submit" class="btn btn-primary" id="assignBtn">
                                        <i class="fa fa-user-plus"></i> Assign Nilai Capaian CPMK
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

</x-app-layout>

@section('script')
<script>
    $(document).ready(function() {
        console.log('✅ Document ready');
        
        // File preview functionality
        $('#file_pdf').change(function() {
            const file = this.files[0];
            if (file) {
                const fileName = file.name;
                const fileSize = (file.size / 1024 / 1024).toFixed(2) + ' MB';
                
                $('#fileName').text(fileName);
                $('#fileSize').text(fileSize);
                $('#filePreview').show();
            } else {
                $('#filePreview').hide();
            }
            validateForm();
        });

        // Form validation
        $('form').submit(function() {
            const fileInput = $('#file_pdf')[0];
            const file = fileInput.files[0];
            
            if (file && file.size > 10 * 1024 * 1024) { // 10MB
                alert('Ukuran file terlalu besar. Maksimal 10MB.');
                return false;
            }
            
            return true;
        });

        // Mahasiswa selection
        $('#mahasiswa_id').change(function() {
            const selectedOption = $(this).find('option:selected');
            const nim = selectedOption.data('nim');
            const name = selectedOption.data('name');
            const email = selectedOption.data('email');
            
            if (nim && name) {
                // Show mahasiswa info
                $('#info-nim').text(nim);
                $('#info-name').text(name);
                $('#info-email').text(email);
                $('#infoMahasiswa').show();
            } else {
                $('#infoMahasiswa').hide();
            }
            
            validateForm();
        });


        // Simple form validation
        function validateForm() {
            const mahasiswaId = $('#mahasiswa_id').val();
            const file = $('#file_pdf')[0].files[0];
            
            console.log('🔍 Validation Debug:');
            console.log('- Mahasiswa ID:', mahasiswaId);
            console.log('- File selected:', file ? file.name : 'No file');
            
            // Always enable button for now
            $('#assignBtn').prop('disabled', false).removeClass('btn-secondary').addClass('btn-primary');
            console.log('✅ Button enabled');
        }

        // Validate form on input changes
        $('#mahasiswa_id').on('change', validateForm);
        $('#file_pdf').on('change', validateForm);

        // Initial validation
        validateForm();
        
        // Debug button state on page load
        console.log('🚀 Page loaded - Button state:');
        console.log('- Button disabled:', $('#assignBtn').prop('disabled'));
        console.log('- Button classes:', $('#assignBtn').attr('class'));
    });
    
    // Test function
    function testButton() {
        alert('Test button works! JavaScript is functioning.');
        console.log('✅ Test button clicked');
    }
    
    // Add click handler for assign button
    $(document).on('click', '#assignBtn', function(e) {
        console.log('🎯 Assign button clicked!');
        const mahasiswaId = $('#mahasiswa_id').val();
        const file = $('#file_pdf')[0].files[0];
        
        if (!mahasiswaId) {
            alert('Pilih mahasiswa terlebih dahulu!');
            e.preventDefault();
            return false;
        }
        
        if (!file) {
            alert('Pilih file PDF terlebih dahulu!');
            e.preventDefault();
            return false;
        }
        
        console.log('✅ Form validation passed, submitting...');
        return true;
    });
</script>
@endsection 
