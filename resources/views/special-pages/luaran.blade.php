@php
    // Helper untuk @error agar tidak error jika $errors tidak ada
    function safe_error(
        $errors, $key, $slot
    ) {
        if (isset($errors) && $errors->has($key)) {
            echo $slot($errors->first($key));
        }
    }
@endphp
<x-app-layout :assets="$assets ?? []">
<div class="row">
   <div class="col-lg-12">
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
      
      @if(isset($errors) && $errors->any())
         <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
               @foreach($errors->all() as $error)
                  <li>{{ $error }}</li>
               @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
         </div>
      @endif
      
      <div class="card rounded">
         <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
               <h4 class="card-title mb-0">Data Luaran KKN</h4>
               <div class="card-tools">
                  <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#tambahLuaranModal">
                     <i class="fas fa-plus"></i> Tambah Luaran
                  </button>
               </div>
            </div>
         </div>
         <div class="card-body">
            <div class="table-responsive">
               <table class="table table-striped table-hover">
                  <thead class="table-primary">
                     <tr>
                        <th scope="col" width="5%">No</th>
                        <th scope="col" width="8%">NIM Mhs</th>
                        <th scope="col" width="18%">Judul Kegiatan</th>
                        <th scope="col" width="12%">Video Aftermovie</th>
                        <th scope="col" width="12%">Artikel Link</th>
                        <th scope="col" width="12%">File Artikel</th>
                        <th scope="col" width="8%">Status</th>
                        <th scope="col" width="8%">Tanggal Upload</th>
                        <th scope="col" width="9%">Aksi</th>
                     </tr>
                  </thead>
                  <tbody>
                     @forelse($luaran ?? [] as $index => $item)
                     <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="text-center">
                           @if(isset($item->user_nim) && $item->user_nim)
                              <span class="badge bg-primary">{{ $item->user_nim }}</span>
                           @else
                              <span class="badge bg-secondary">-</span>
                           @endif
                        </td>
                        <td>
                           <h6 class="mb-0">{{ $item->judul_kegiatan ?? 'Judul Kegiatan' }}</h6>
                           <small class="text-muted">ID: {{ $item->id ?? 'N/A' }}</small>
                        </td>
                        <td>
                           <a href="{{ $item->video_aftermovie }}" target="_blank" class="text-decoration-none">
                              <i class="fas fa-video me-1"></i>Lihat Video
                           </a>
                        </td>
                        <td>
                           <a href="{{ $item->artikel_link }}" target="_blank" class="text-decoration-none">
                              <i class="fas fa-link me-1"></i>Buka Artikel
                           </a>
                        </td>
                        <td>
                           @if(isset($item->artikel_file_name) && $item->artikel_file_name)
                              <div class="d-flex gap-2">
                                 <button type="button" class="btn btn-sm btn-info text-white shadow-sm" onclick="openPdfViewer('{{ route('files.pdf.luaran', $item->artikel_file_name) }}', '{{ $item->artikel_file_name }}')" title="Lihat File">
                                    <i class="fas fa-eye me-1"></i>Lihat
                                 </button>
                                 <a href="{{ route('files.pdf.luaran.download', $item->artikel_file_name) }}" class="btn btn-sm btn-primary shadow-sm" title="Download File">
                                    <i class="fas fa-download me-1"></i>Download
                                 </a>
                              </div>
                              <small class="text-muted d-block mt-1">{{ $item->artikel_file_name }}</small>
                           @else
                              <span class="badge bg-secondary">Tidak ada file</span>
                           @endif
                        </td>
                        <td>
                           @if(isset($item->status))
                              @if($item->status == 'approved')
                                 <span class="badge bg-success">Disetujui</span>
                              @elseif($item->status == 'rejected')
                                 <span class="badge bg-danger">Ditolak</span>
                              @elseif($item->status == 'pending')
                                 <span class="badge bg-warning">Menunggu</span>
                              @endif
                           @else
                              <span class="badge bg-secondary">Belum Diverifikasi</span>
                           @endif
                        </td>
                        <td>{{ $item->created_at ? $item->created_at->format('d/m/Y H:i') : 'N/A' }}</td>
                        <td>
                           <div class="d-flex gap-1">
                              <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#detailModal{{ $item->id ?? $index }}" title="Detail">
                                 <i class="fas fa-eye me-1"></i>Detail
                              </button>
                              <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal{{ $item->id ?? $index }}" title="Edit">
                                 <i class="fas fa-edit me-1"></i>Edit
                              </button>
                              <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#verifikasiModal{{ $item->id ?? $index }}" title="Verifikasi">
                                 <i class="fas fa-check me-1"></i>Verifikasi
                              </button>
                              <form action="{{ route('luaran.destroy', $item->id ?? $index) }}" method="POST" style="display: inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data luaran ini?')">
                                 @csrf
                                 @method('DELETE')
                                 <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                    <i class="fas fa-trash me-1"></i>Hapus
                                 </button>
                              </form>
                           </div>
                        </td>
                     </tr>
                     @empty
                     <tr>
                        <td colspan="9" class="text-center">
                           <div class="py-4">
                              <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                              <p class="text-muted">Belum ada data luaran</p>
                              <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahLuaranModal">
                                 <i class="fas fa-plus"></i> Tambah Luaran Pertama
                              </button>
                           </div>
                        </td>
                     </tr>
                     @endforelse
                  </tbody>
               </table>
            </div>
            
            @if(isset($luaran) && $luaran->count() > 0)
            <div class="d-flex justify-content-between align-items-center mt-3">
               <div class="text-muted">
                  Menampilkan 1 - {{ $luaran->count() }} dari {{ $luaran->count() }} data
               </div>
            </div>
            @endif
         </div>
      </div>
   </div>
</div>

<!-- Modal Tambah Luaran -->
<div class="modal fade" id="tambahLuaranModal" tabindex="-1" aria-labelledby="tambahLuaranModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-lg">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title" id="tambahLuaranModalLabel">Tambah Luaran Baru</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <form action="{{ route('luaran.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-body">
               <!-- Judul Kegiatan -->
               <div class="mb-3">
                  <label for="judul_kegiatan" class="form-label">Judul Kegiatan</label>
                  <input type="text" class="form-control @if(isset($errors) && $errors->has('judul_kegiatan')) is-invalid @endif" id="judul_kegiatan" name="judul_kegiatan" value="{{ old('judul_kegiatan') }}" placeholder="Masukkan judul kegiatan KKN" required>
                  @php safe_error($errors, 'judul_kegiatan', function($msg) { echo '<div class="invalid-feedback">' . $msg . '</div>'; }); @endphp
               </div>

               <!-- Video Aftermovie -->
               <div class="mb-3">
                  <label for="video_aftermovie" class="form-label">Video Aftermovie</label>
                  <input type="url" class="form-control @if(isset($errors) && $errors->has('video_aftermovie')) is-invalid @endif" id="video_aftermovie" name="video_aftermovie" value="{{ old('video_aftermovie') }}" placeholder="Tautan Video Aftermovie" required>
                  @php safe_error($errors, 'video_aftermovie', function($msg) { echo '<div class="invalid-feedback">' . $msg . '</div>'; }); @endphp
               </div>

               <!-- Artikel Link -->
               <div class="mb-3">
                  <label for="artikel_link" class="form-label">Artikel Link</label>
                  <input type="url" class="form-control @if(isset($errors) && $errors->has('artikel_link')) is-invalid @endif" id="artikel_link" name="artikel_link" value="{{ old('artikel_link') }}" placeholder="Tautan Artikel di laman ITK" required>
                  @php safe_error($errors, 'artikel_link', function($msg) { echo '<div class="invalid-feedback">' . $msg . '</div>'; }); @endphp
               </div>

               <!-- Upload Artikel File -->
               <div class="mb-3">
                  <label for="artikel_file" class="form-label">Upload Artikel</label>
                  <input type="file" class="form-control @if(isset($errors) && $errors->has('artikel_file')) is-invalid @endif" id="artikel_file" name="artikel_file" accept=".pdf,.doc,.docx" required>
                  <small class="text-muted">Format yang diperbolehkan: PDF, DOC, DOCX (maksimal 10MB)</small>
                  @php safe_error($errors, 'artikel_file', function($msg) { echo '<div class="invalid-feedback">' . $msg . '</div>'; }); @endphp
               </div>

               <!-- NIM Input -->
               <div class="mb-3">
                  <label for="user_nim" class="form-label">NIM Input</label>
                  <input type="text" class="form-control @if(isset($errors) && $errors->has('user_nim')) is-invalid @endif" id="user_nim" name="user_nim" value="{{ old('user_nim', auth()->user()->nim ?? '') }}" placeholder="NIM User Input" required>
                  @php safe_error($errors, 'user_nim', function($msg) { echo '<div class="invalid-feedback">' . $msg . '</div>'; }); @endphp
               </div>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
               <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
         </form>
      </div>
   </div>
</div>

<!-- Modal Detail Luaran -->
@if(isset($luaran))
@foreach($luaran as $index => $item)
<div class="modal fade" id="detailModal{{ $item->id ?? $index }}" tabindex="-1" aria-labelledby="detailModalLabel{{ $item->id ?? $index }}" aria-hidden="true">
   <div class="modal-dialog modal-lg">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title" id="detailModalLabel{{ $item->id ?? $index }}">Detail Luaran</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <div class="modal-body">
            <div class="row">
               <div class="col-md-12 mb-3">
                  <strong>Judul Kegiatan:</strong>
                  <p class="fw-bold text-primary">{{ $item->judul_kegiatan ?? 'Judul Kegiatan' }}</p>
               </div>
               <div class="col-md-12 mb-3">
                  <strong>Video Aftermovie:</strong>
                  <p>
                     <a href="{{ $item->video_aftermovie }}" target="_blank" class="text-decoration-none">
                        <i class="fas fa-video me-1"></i>{{ $item->video_aftermovie }}
                     </a>
                  </p>
               </div>
               <div class="col-md-12 mb-3">
                  <strong>Artikel Link:</strong>
                  <p>
                     <a href="{{ $item->artikel_link }}" target="_blank" class="text-decoration-none">
                        <i class="fas fa-link me-1"></i>{{ $item->artikel_link }}
                     </a>
                  </p>
               </div>
               @if(isset($item->artikel_file_name) && $item->artikel_file_name)
               <div class="col-md-12 mb-3">
                  <strong>File Artikel:</strong>
                  <div class="d-flex gap-2 align-items-center flex-wrap">
                     <a href="{{ route('files.pdf.luaran', $item->artikel_file_name) }}" 
                        target="_blank" 
                        class="btn btn-sm btn-info text-white shadow-sm" 
                        title="Lihat File">
                        <i class="fas fa-eye me-1"></i>Lihat File
                     </a>
                     <a href="{{ route('files.pdf.luaran.download', $item->artikel_file_name) }}" 
                        download="{{ $item->artikel_file_name }}"
                        class="btn btn-sm btn-primary shadow-sm" 
                        title="Download File">
                        <i class="fas fa-download me-1"></i>Download File
                     </a>
                     <span class="badge bg-light text-dark border">{{ $item->artikel_file_name }}</span>
                  </div>
               </div>
               @endif
               <div class="col-md-6 mb-3">
                  <strong>Status:</strong>
                  <p>
                     @if(isset($item->status))
                        @if($item->status == 'approved')
                           <span class="badge bg-success">Disetujui</span>
                        @elseif($item->status == 'rejected')
                           <span class="badge bg-danger">Ditolak</span>
                        @elseif($item->status == 'pending')
                           <span class="badge bg-warning">Menunggu</span>
                        @endif
                     @else
                        <span class="badge bg-secondary">Belum Diverifikasi</span>
                     @endif
                  </p>
               </div>
               <div class="col-md-6 mb-3">
                  <strong>Tanggal Upload:</strong>
                  <p>{{ $item->created_at ? $item->created_at->format('d/m/Y H:i') : 'N/A' }}</p>
               </div>
               <div class="col-md-6 mb-3">
                  <strong>NIM Input:</strong>
                  <p>
                     @if(isset($item->user_nim) && $item->user_nim)
                        <span class="badge bg-primary">{{ $item->user_nim }}</span>
                     @else
                        <span class="badge bg-secondary">-</span>
                     @endif
                  </p>
               </div>
               @if(isset($item->catatan) && $item->catatan)
               <div class="col-md-12 mb-3">
                  <strong>Catatan:</strong>
                  <p class="text-muted">{{ $item->catatan }}</p>
               </div>
               @endif
            </div>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
         </div>
      </div>
   </div>
</div>

<!-- Modal Edit Luaran -->
<div class="modal fade" id="editModal{{ $item->id ?? $index }}" tabindex="-1" aria-labelledby="editModalLabel{{ $item->id ?? $index }}" aria-hidden="true">
   <div class="modal-dialog modal-lg">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title" id="editModalLabel{{ $item->id ?? $index }}">Edit Luaran</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <form action="{{ route('luaran.update', $item->id ?? $index) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="modal-body">
               <!-- Judul Kegiatan -->
               <div class="mb-3">
                  <label for="edit_judul_kegiatan_{{ $item->id ?? $index }}" class="form-label">Judul Kegiatan</label>
                  <input type="text" class="form-control" id="edit_judul_kegiatan_{{ $item->id ?? $index }}" name="judul_kegiatan" value="{{ $item->judul_kegiatan ?? '' }}" placeholder="Masukkan judul kegiatan KKN" required>
               </div>

               <!-- Video Aftermovie -->
               <div class="mb-3">
                  <label for="edit_video_aftermovie_{{ $item->id ?? $index }}" class="form-label">Video Aftermovie</label>
                  <input type="url" class="form-control" id="edit_video_aftermovie_{{ $item->id ?? $index }}" name="video_aftermovie" value="{{ $item->video_aftermovie }}" placeholder="Tautan Video Aftermovie" required>
               </div>

               <!-- Artikel Link -->
               <div class="mb-3">
                  <label for="edit_artikel_link_{{ $item->id ?? $index }}" class="form-label">Artikel Link</label>
                  <input type="url" class="form-control" id="edit_artikel_link_{{ $item->id ?? $index }}" name="artikel_link" value="{{ $item->artikel_link }}" placeholder="Tautan Artikel di laman ITK" required>
               </div>

               <!-- Upload Artikel File -->
               <div class="mb-3">
                  <label for="edit_artikel_file_{{ $item->id ?? $index }}" class="form-label">Upload Artikel (Opsional)</label>
                  <input type="file" class="form-control" id="edit_artikel_file_{{ $item->id ?? $index }}" name="artikel_file" accept=".pdf,.doc,.docx">
                  <small class="text-muted">Format yang diperbolehkan: PDF, DOC, DOCX (maksimal 10MB). Kosongkan jika tidak ingin mengubah file.</small>
                  @if(isset($item->artikel_file_name) && $item->artikel_file_name)
                  <div class="mt-2">
                     <small class="text-muted">File saat ini: {{ $item->artikel_file_name }}</small>
                  </div>
                  @endif
               </div>

               <!-- NIM Input -->
               <div class="mb-3">
                  <label for="edit_user_nim_{{ $item->id ?? $index }}" class="form-label">NIM Input</label>
                  <input type="text" class="form-control" id="edit_user_nim_{{ $item->id ?? $index }}" name="user_nim" value="{{ $item->user_nim ?? auth()->user()->nim ?? '' }}" placeholder="NIM User Input" required>
               </div>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
               <button type="submit" class="btn btn-primary">Update</button>
            </div>
         </form>
      </div>
   </div>
</div>

<!-- Modal Verifikasi Luaran -->
<div class="modal fade" id="verifikasiModal{{ $item->id ?? $index }}" tabindex="-1" aria-labelledby="verifikasiModalLabel{{ $item->id ?? $index }}" aria-hidden="true">
   <div class="modal-dialog">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title" id="verifikasiModalLabel{{ $item->id ?? $index }}">Verifikasi Luaran</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <form action="{{ route('luaran.verifikasi', $item->id ?? $index) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
               <div class="mb-3">
                  <label for="status_{{ $item->id ?? $index }}" class="form-label">Status Verifikasi</label>
                  <select class="form-select" id="status_{{ $item->id ?? $index }}" name="status" required>
                     <option value="pending" {{ $item->status == 'pending' ? 'selected' : '' }}>Menunggu</option>
                     <option value="approved" {{ $item->status == 'approved' ? 'selected' : '' }}>Disetujui</option>
                     <option value="rejected" {{ $item->status == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                  </select>
               </div>
               <div class="mb-3">
                  <label for="catatan_{{ $item->id ?? $index }}" class="form-label">Catatan (Opsional)</label>
                  <textarea class="form-control" id="catatan_{{ $item->id ?? $index }}" name="catatan" rows="3" placeholder="Catatan verifikasi...">{{ $item->catatan ?? '' }}</textarea>
               </div>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
               <button type="submit" class="btn btn-success">Verifikasi</button>
            </div>
         </form>
      </div>
   </div>
</div>
@endforeach
@endif

<!-- Modal PDF Viewer -->
<div class="modal fade" id="pdfViewerModal" tabindex="-1" aria-labelledby="pdfViewerModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
   <div class="modal-dialog modal-fullscreen">
      <div class="modal-content">
         <div class="modal-header pdf-viewer-header">
            <h5 class="modal-title" id="pdfViewerModalLabel">
               <i class="fas fa-file-pdf me-2"></i>
               <span id="pdfFileName">File PDF</span>
            </h5>
            <div class="d-flex gap-2">
               <a id="pdfDownloadLink" href="" target="_blank" class="btn btn-light btn-sm">
                  <i class="fas fa-download me-1"></i>Download
               </a>
               <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal" aria-label="Close">
                  <i class="fas fa-times"></i>
               </button>
            </div>
         </div>
         <div class="modal-body p-0">
            <div id="pdfLoading" class="d-flex justify-content-center align-items-center" style="height: calc(100vh - 120px);">
               <div class="text-center">
                  <div class="spinner-border text-primary" role="status">
                     <span class="visually-hidden">Loading...</span>
                  </div>
                  <p class="mt-2 text-muted">Memuat PDF...</p>
               </div>
            </div>
            <div id="pdfContent" class="d-none">
               <iframe id="pdfViewer" src="" frameborder="0" style="width: 100%; height: calc(100vh - 120px);"></iframe>
            </div>
         </div>
      </div>
   </div>
</div>

<style>
.modal-fullscreen {
   max-width: 100vw !important;
   width: 100vw !important;
   margin: 0 !important;
   height: 100vh !important;
}

.modal-fullscreen .modal-content {
   height: 100vh !important;
   border-radius: 0 !important;
}

#pdfViewer {
   width: 100%;
   height: calc(100vh - 120px);
   border: none;
}

.pdf-viewer-header {
   background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
   color: white;
   border-bottom: none;
   padding: 1rem 1.5rem;
}

.pdf-viewer-header .btn-light {
   background: rgba(255, 255, 255, 0.2);
   border: 1px solid rgba(255, 255, 255, 0.3);
   color: white;
}

.pdf-viewer-header .btn-light:hover {
   background: rgba(255, 255, 255, 0.3);
   border: 1px solid rgba(255, 255, 255, 0.4);
   color: white;
}

.btn-info {
   background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
   border: none;
}

.btn-info:hover {
   background: linear-gradient(135deg, #138496 0%, #117a8b 100%);
   transform: translateY(-1px);
   box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.btn-primary {
   background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
   border: none;
}

.btn-primary:hover {
   background: linear-gradient(135deg, #0056b3 0%, #004085 100%);
   transform: translateY(-1px);
   box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.btn-warning {
   background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
   border: none;
   color: #212529;
}

.btn-warning:hover {
   background: linear-gradient(135deg, #e0a800 0%, #d39e00 100%);
   transform: translateY(-1px);
   box-shadow: 0 4px 8px rgba(0,0,0,0.2);
   color: #212529;
}

.btn-success {
   background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
   border: none;
}

.btn-success:hover {
   background: linear-gradient(135deg, #1e7e34 0%, #1c7430 100%);
   transform: translateY(-1px);
   box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.btn-danger {
   background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
   border: none;
}

.btn-danger:hover {
   background: linear-gradient(135deg, #c82333 0%, #bd2130 100%);
   transform: translateY(-1px);
   box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.shadow-sm {
   box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
}

.badge {
   font-size: 0.75rem;
   padding: 0.5em 0.75em;
}

.text-decoration-none:hover {
   text-decoration: underline !important;
}

@media (max-width: 768px) {
   .pdf-viewer-header h5 {
      font-size: 1rem;
   }
}
</style>

<script>
// Function untuk membuka PDF viewer
function openPdfViewer(pdfUrl, fileName) {
   console.log('Opening PDF viewer:', { pdfUrl, fileName });
   
   const modal = new bootstrap.Modal(document.getElementById('pdfViewerModal'));
   const iframe = document.getElementById('pdfViewer');
   const fileNameSpan = document.getElementById('pdfFileName');
   const downloadLink = document.getElementById('pdfDownloadLink');
   const loadingDiv = document.getElementById('pdfLoading');
   const contentDiv = document.getElementById('pdfContent');
   
   // Reset modal state
   loadingDiv.classList.remove('d-none');
   contentDiv.classList.add('d-none');
   iframe.src = '';
   
   // Set file name and download link
   fileNameSpan.textContent = fileName;
   downloadLink.href = pdfUrl;
   
   // Show modal
   modal.show();
   
   // Check if URL is valid
   if (!pdfUrl || pdfUrl === '') {
      loadingDiv.innerHTML = `
         <div class="text-center">
            <i class="fas fa-exclamation-triangle text-warning fa-3x mb-3"></i>
            <p class="text-muted">URL file tidak valid.</p>
         </div>
      `;
      return;
   }
   
   // Test if file exists first
   fetch(pdfUrl, { method: 'HEAD' })
      .then(response => {
         if (response.ok) {
            console.log('File exists, loading PDF...');
            
            // Set iframe src with parameters for better PDF display
            const iframeSrc = pdfUrl + '#toolbar=1&navpanes=1&scrollbar=1&view=FitH';
            iframe.src = iframeSrc;
            
            // Hide loading and show content when iframe loads
            iframe.onload = function() {
               console.log('Iframe loaded successfully');
               setTimeout(() => {
                  loadingDiv.classList.add('d-none');
                  contentDiv.classList.remove('d-none');
               }, 1000);
            };
            
            // Handle iframe load error
            iframe.onerror = function() {
               console.error('Iframe load error');
               loadingDiv.innerHTML = `
                  <div class="text-center">
                     <i class="fas fa-exclamation-triangle text-warning fa-3x mb-3"></i>
                     <p class="text-muted">Gagal memuat PDF. Silakan coba download file.</p>
                     <a href="${pdfUrl}" target="_blank" class="btn btn-primary">
                        <i class="fas fa-download me-1"></i>Download PDF
                     </a>
                  </div>
               `;
            };
            
            // Add timeout for loading
            setTimeout(() => {
               if (!loadingDiv.classList.contains('d-none')) {
                  console.log('Loading timeout, showing content anyway');
                  loadingDiv.classList.add('d-none');
                  contentDiv.classList.remove('d-none');
               }
            }, 5000);
            
         } else {
            console.error('File not found:', response.status);
            loadingDiv.innerHTML = `
               <div class="text-center">
                  <i class="fas fa-exclamation-triangle text-warning fa-3x mb-3"></i>
                  <p class="text-muted">File PDF tidak ditemukan di server.</p>
                  <p class="text-muted small">Status: ${response.status} ${response.statusText}</p>
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                     <i class="fas fa-times me-1"></i>Tutup
                  </button>
               </div>
            `;
         }
      })
      .catch(error => {
         console.error('Fetch error:', error);
         loadingDiv.innerHTML = `
            <div class="text-center">
               <i class="fas fa-exclamation-triangle text-warning fa-3x mb-3"></i>
               <p class="text-muted">Gagal mengakses file PDF.</p>
               <p class="text-muted small">Error: ${error.message}</p>
               <a href="${pdfUrl}" target="_blank" class="btn btn-primary">
                  <i class="fas fa-download me-1"></i>Download PDF
               </a>
            </div>
         `;
      });
}

document.addEventListener('DOMContentLoaded', function() {
   console.log('Luaran page loaded');
   
   // Debug: Log semua form delete
   const deleteForms = document.querySelectorAll('form[action*="luaran/destroy"]');
   console.log('Found delete forms:', deleteForms.length);
   
   deleteForms.forEach((form, index) => {
      console.log(`Delete form ${index + 1}:`, form);
   });

   // Validasi form luaran
   const luaranForm = document.getElementById('tambahLuaranModal');
   if (luaranForm) {
      const form = luaranForm.querySelector('form');
      const fileInput = form.querySelector('input[name="artikel_file"]');
      
      form.addEventListener('submit', function(e) {
         // Validasi file
         if (fileInput.files.length === 0) {
            e.preventDefault();
            alert('File artikel wajib diupload!');
            return;
         }

         const file = fileInput.files[0];
         if (file.type !== 'application/pdf' && file.type !== 'application/msword' && file.type !== 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' || file.size > 10 * 1024 * 1024) {
            e.preventDefault();
            alert('Hanya file PDF, DOC, atau DOCX maksimal 10 MB yang diperbolehkan!');
            return;
         }
      });

      // File validation
      fileInput.addEventListener('change', function() {
         if (fileInput.files.length > 0) {
            const file = fileInput.files[0];
            if (file.type !== 'application/pdf' && file.type !== 'application/msword' && file.type !== 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' || file.size > 10 * 1024 * 1024) {
               alert('Hanya file PDF, DOC, atau DOCX maksimal 10 MB yang diperbolehkan!');
               fileInput.value = '';
            }
         }
      });
   }

   // Event listener untuk modal PDF viewer
   const pdfModal = document.getElementById('pdfViewerModal');
   if (pdfModal) {
      pdfModal.addEventListener('hidden.bs.modal', function() {
         // Clear iframe src when modal is closed
         const iframe = document.getElementById('pdfViewer');
         const loadingDiv = document.getElementById('pdfLoading');
         const contentDiv = document.getElementById('pdfContent');
         
         iframe.src = '';
         loadingDiv.classList.remove('d-none');
         contentDiv.classList.add('d-none');
         
         // Reset loading content
         loadingDiv.innerHTML = `
            <div class="text-center">
               <div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">Loading...</span>
               </div>
               <p class="mt-2 text-muted">Memuat PDF...</p>
            </div>
         `;
      });
   }
});
</script>
</x-app-layout> 
