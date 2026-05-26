<x-app-layout :assets="$assets ?? []">
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">Data Proposal Kegiatan</h4>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahProposalModal">
                        <i class="fas fa-plus me-2"></i>Tambah Proposal Baru
                    </button>
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

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-primary">
                                <tr>
                                    <th scope="col" width="5%">No</th>
                                    <th scope="col" width="8%">NIM Mhs</th>
                                    <th scope="col" width="18%">Judul Kegiatan</th>
                                    <th scope="col" width="12%">File Proposal</th>
                                    <th scope="col" width="8%">Status</th>
                                    <th scope="col" width="8%">Tanggal Upload</th>
                                    <th scope="col" width="11%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($proposals ?? [] as $index => $item)
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
                                        @if(isset($item->file_name) && $item->file_name)
                                            <div class="d-flex gap-2">
                                                <button type="button" class="btn btn-sm btn-info text-white shadow-sm" onclick="openPdfViewer('{{ route('files.pdf.proposal', $item->file_name) }}', '{{ $item->file_name }}')" title="Lihat PDF">
                                                    <i class="fas fa-eye me-1"></i>Lihat
                                                </button>
                                                <a href="{{ route('files.pdf.proposal.download', $item->file_name) }}" class="btn btn-sm btn-primary shadow-sm" title="Download PDF">
                                                    <i class="fas fa-download me-1"></i>Download
                                                </a>
                                            </div>
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
                                            @else
                                                <span class="badge bg-warning">Pending</span>
                                            @endif
                                        @else
                                            <span class="badge bg-secondary">-</span>
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
                                            <form action="{{ route('proposal.destroy', $item->id ?? $index) }}" method="POST" style="display: inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data proposal ini?')">
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
                                    <td colspan="7" class="text-center">
                                        <div class="py-4">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">Belum ada data proposal</p>
                                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahProposalModal">
                                                <i class="fas fa-plus"></i> Tambah Proposal Pertama
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    @if(isset($proposals) && $proposals->count() > 0)
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted">
                            Menampilkan 1 - {{ $proposals->count() }} dari {{ $proposals->count() }} data
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Proposal -->
<div class="modal fade" id="tambahProposalModal" tabindex="-1" aria-labelledby="tambahProposalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tambahProposalModalLabel">Tambah Proposal Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('proposal.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="user_nim" value="{{ auth()->user()->nim ?? '10221051' }}">
                    
                    <div class="mb-3">
                        <label for="judul_kegiatan" class="form-label">Judul Kegiatan</label>
                        <input type="text" class="form-control @error('judul_kegiatan') is-invalid @enderror" id="judul_kegiatan" name="judul_kegiatan" value="{{ old('judul_kegiatan') }}" required>
                        @error('judul_kegiatan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="file" class="form-label">File Proposal (PDF)</label>
                        <input type="file" class="form-control @error('file') is-invalid @enderror" id="file" name="file" accept=".pdf" required>
                        <small class="text-muted">Maksimal 10MB, format PDF</small>
                        @error('file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
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

<!-- Edit Proposal Modals -->
@foreach($proposals ?? [] as $item)
<div class="modal fade" id="editModal{{ $item->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $item->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel{{ $item->id }}">Edit Proposal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('proposal.update', $item->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <input type="hidden" name="user_nim" value="{{ auth()->user()->nim ?? '10221051' }}">
                    
                    <div class="mb-3">
                        <label for="judul_kegiatan{{ $item->id }}" class="form-label">Judul Kegiatan</label>
                        <input type="text" class="form-control" id="judul_kegiatan{{ $item->id }}" name="judul_kegiatan" value="{{ $item->judul_kegiatan }}" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="file{{ $item->id }}" class="form-label">File Proposal (PDF)</label>
                        <input type="file" class="form-control" id="file{{ $item->id }}" name="file" accept=".pdf">
                        <small class="text-muted">Maksimal 10MB, format PDF. Kosongkan jika tidak ingin mengubah file.</small>
                        @if($item->file_name)
                            <div class="mt-2">
                                <small class="text-info">File saat ini: {{ $item->file_name }}</small>
                            </div>
                        @endif
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

<!-- Verifikasi Proposal Modals -->
<div class="modal fade" id="verifikasiModal{{ $item->id }}" tabindex="-1" aria-labelledby="verifikasiModalLabel{{ $item->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="verifikasiModalLabel{{ $item->id }}">Verifikasi Proposal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('proposal.verifikasi', $item->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="status{{ $item->id }}" class="form-label">Status</label>
                        <select class="form-select" id="status{{ $item->id }}" name="status" required>
                            <option value="pending" {{ $item->status == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ $item->status == 'approved' ? 'selected' : '' }}>Disetujui</option>
                            <option value="rejected" {{ $item->status == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="catatan{{ $item->id }}" class="form-label">Catatan</label>
                        <textarea class="form-control" id="catatan{{ $item->id }}" name="catatan" rows="3">{{ $item->catatan }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Detail Proposal Modals -->
<div class="modal fade" id="detailModal{{ $item->id }}" tabindex="-1" aria-labelledby="detailModalLabel{{ $item->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailModalLabel{{ $item->id }}">Detail Proposal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>ID:</strong> {{ $item->id }}</p>
                        <p><strong>Judul Kegiatan:</strong> {{ $item->judul_kegiatan }}</p>
                        <p><strong>NIM:</strong> {{ $item->user_nim }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Status:</strong> 
                            @if($item->status == 'approved')
                                <span class="badge bg-success">Disetujui</span>
                            @elseif($item->status == 'rejected')
                                <span class="badge bg-danger">Ditolak</span>
                            @else
                                <span class="badge bg-warning">Pending</span>
                            @endif
                        </p>
                        <p><strong>Tanggal Upload:</strong> {{ $item->created_at ? $item->created_at->format('d/m/Y H:i') : 'N/A' }}</p>
                        <p><strong>File:</strong> {{ $item->file_name ?? 'Tidak ada file' }}</p>
                    </div>
                </div>
                @if($item->catatan)
                <div class="mt-3">
                    <p><strong>Catatan:</strong></p>
                    <p class="text-muted">{{ $item->catatan }}</p>
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

.shadow-sm {
   box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
}
</style>

</x-app-layout>

<script>
console.log('=== PROPOSAL SCRIPT LOADED (DIRECT) ===');
console.log('Bootstrap available:', typeof bootstrap !== 'undefined');
console.log('openPdfViewer function:', typeof openPdfViewer);

// Check if modal exists
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded (direct)');
    const modal = document.getElementById('pdfViewerModal');
    console.log('Modal element found:', modal);
    if (modal) {
        console.log('Modal HTML:', modal.outerHTML.substring(0, 200) + '...');
    }
});

// Function untuk membuka PDF viewer
function openPdfViewer(pdfUrl, fileName) {
   console.log('=== OPEN PDF VIEWER CALLED (DIRECT) ===');
   console.log('pdfUrl:', pdfUrl);
   console.log('fileName:', fileName);
   
   const modal = new bootstrap.Modal(document.getElementById('pdfViewerModal'));
   const iframe = document.getElementById('pdfViewer');
   const fileNameSpan = document.getElementById('pdfFileName');
   const downloadLink = document.getElementById('pdfDownloadLink');
   const loadingDiv = document.getElementById('pdfLoading');
   const contentDiv = document.getElementById('pdfContent');
   
   console.log('Modal element:', document.getElementById('pdfViewerModal'));
   console.log('Iframe element:', iframe);
   console.log('FileName span:', fileNameSpan);
   
   // Reset modal state
   loadingDiv.classList.remove('d-none');
   contentDiv.classList.add('d-none');
   iframe.src = '';
   
   // Set file name and download link
   fileNameSpan.textContent = fileName;
   downloadLink.href = pdfUrl;
   
   // Show modal
   modal.show();
   console.log('Modal.show() called (direct)');
   
   // Check if URL is valid
   if (!pdfUrl || pdfUrl === '') {
      console.log('URL is empty or invalid');
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
         console.log('Fetch response:', response.status, response.statusText);
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

console.log('=== PROPOSAL SCRIPT END (DIRECT) ===');
</script> 
