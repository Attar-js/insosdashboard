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
      
      <div class="card rounded">
         <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
               <h4 class="card-title mb-0">Data Pendaftar Kegiatan</h4>
               <div class="card-tools">
                  <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#tambahPendaftarModal">
                     <i class="fas fa-plus"></i> Tambah Pendaftar
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
                        <th scope="col" width="10%">Mitra</th>
                        <th scope="col" width="8%">Lokasi Mitra</th>
                        <th scope="col" width="8%">Jumlah Anggota</th>
                        <th scope="col" width="8%">Tanggal Daftar</th>
                        <th scope="col" width="8%">Status</th>
                        <th scope="col" width="8%">File</th>
                        <th scope="col" width="11%">Aksi</th>
                     </tr>
                  </thead>
                  <tbody>
                     @forelse($pendaftar ?? [] as $index => $item)
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
                           <h6 class="mb-0">{{ $item->judul_kegiatan ?? 'Judul Kegiatan Sample' }}</h6>
                           <small class="text-muted">Didaftar: {{ $item->created_at ?? now()->format('d/m/Y H:i') }}</small>
                        </td>
                        <td>
                           <span class="badge bg-info">{{ $item->mitra ?? 'Mitra A' }}</span>
                        </td>
                        <td>
                           <small class="text-muted">{{ $item->lokasi_mitra ?? 'Lokasi Mitra' }}</small>
                        </td>
                        <td class="text-center">
                           <span class="badge bg-success">{{ $item->jumlah_anggota ?? '5' }} Orang</span>
                        </td>
                        <td>{{ $item->tanggal_daftar ?? '2024-01-15' }}</td>
                        <td>
                           @if(isset($item->status_verifikasi))
                              @if($item->status_verifikasi == 'diterima')
                                 <span class="badge bg-success">Diterima</span>
                              @elseif($item->status_verifikasi == 'ditolak')
                                 <span class="badge bg-danger">Ditolak</span>
                              @elseif($item->status_verifikasi == 'pending')
                                 <span class="badge bg-warning">Pending</span>
                              @endif
                           @else
                              <span class="badge bg-secondary">Belum Diverifikasi</span>
                           @endif
                        </td>
                        <td>
                           @if(isset($item->file_name) && $item->file_name)
                              <div class="d-flex gap-2">
                                 <button type="button" class="btn btn-sm btn-info text-white shadow-sm" onclick="openPdfViewer('{{ route('files.pdf.dashboard', $item->file_name) }}', '{{ $item->file_name }}')" title="Lihat PDF">
                                    <i class="fas fa-eye me-1"></i>Lihat
                                 </button>
                                 <a href="{{ route('files.pdf.dashboard.download', $item->file_name) }}" class="btn btn-sm btn-primary shadow-sm" title="Download PDF">
                                    <i class="fas fa-download me-1"></i>Download
                                 </a>
                              </div>
                           @else
                              <span class="badge bg-secondary">Tidak ada file</span>
                           @endif
                        </td>
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
                              <form action="{{ route('pendaftar.destroy', $item->id ?? $index) }}" method="POST" style="display: inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data pendaftar ini?')">
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
                        <td colspan="10" class="text-center">
                           <div class="py-4">
                              <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                              <p class="text-muted">Belum ada data pendaftar</p>
                              <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahPendaftarModal">
                                 <i class="fas fa-plus"></i> Tambah Pendaftar Pertama
                              </button>
                           </div>
                        </td>
                     </tr>
                     @endforelse
                  </tbody>
               </table>
            </div>
            
            @if(isset($pendaftar) && $pendaftar->count() > 0)
            <div class="d-flex justify-content-between align-items-center mt-3">
               <div class="text-muted">
                  Menampilkan 1 - {{ $pendaftar->count() }} dari {{ $pendaftar->count() }} data
               </div>
            </div>
            @endif
         </div>
      </div>
   </div>
</div>

<!-- Modal Tambah Pendaftar -->
<div class="modal fade" id="tambahPendaftarModal" tabindex="-1" aria-labelledby="tambahPendaftarModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-xl">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title" id="tambahPendaftarModalLabel">Tambah Pendaftar Baru</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <form action="{{ route('pendaftar.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-body">
               <!-- Judul Kegiatan -->
               <div class="row">
                  <div class="col-md-12 mb-3">
                     <label for="judul_kegiatan" class="form-label">Judul Kegiatan</label>
                     <input type="text" class="form-control @error('judul_kegiatan') is-invalid @enderror" id="judul_kegiatan" name="judul_kegiatan" value="{{ old('judul_kegiatan') }}" placeholder="Judul Kegiatan" required>
                     @error('judul_kegiatan')
                        <div class="invalid-feedback">{{ $message }}</div>
                     @enderror
                  </div>
               </div>

               <!-- Mitra & Lokasi -->
               <div class="row">
                  <div class="col-md-4 mb-3">
                     <label for="mitra" class="form-label">Nama Mitra</label>
                     <input type="text" class="form-control @error('mitra') is-invalid @enderror" id="mitra" name="mitra" value="{{ old('mitra') }}" placeholder="Nama Mitra" required>
                     @error('mitra')
                        <div class="invalid-feedback">{{ $message }}</div>
                     @enderror
                  </div>
                  <div class="col-md-4 mb-3">
                     <label for="lokasi_mitra" class="form-label">Lokasi Mitra</label>
                     <input type="text" class="form-control @error('lokasi_mitra') is-invalid @enderror" id="lokasi_mitra" name="lokasi_mitra" value="{{ old('lokasi_mitra') }}" placeholder="Lokasi Mitra" required>
                     @error('lokasi_mitra')
                        <div class="invalid-feedback">{{ $message }}</div>
                     @enderror
                  </div>
                  <div class="col-md-4 mb-3">
                     <label for="user_nim" class="form-label">NIM Input</label>
                     <input type="text" class="form-control @error('user_nim') is-invalid @enderror" id="user_nim" name="user_nim" value="{{ old('user_nim', auth()->user()->nim ?? '') }}" placeholder="NIM User Input" required>
                     @error('user_nim')
                        <div class="invalid-feedback">{{ $message }}</div>
                     @enderror
                  </div>
               </div>

               <!-- Header Anggota -->
               <div class="row mb-2 fw-bold text-center small">
                  <div class="col-md-3">Nama</div>
                  <div class="col-md-3">NIM</div>
                  <div class="col-md-3">Program Studi</div>
                  <div class="col-md-3">Peran</div>
               </div>

               <!-- Anggota Kelompok -->
               @for ($i = 1; $i <= 10; $i++)
               <div class="row g-2 align-items-center mb-2">
                  <div class="col-md-3">
                     <input type="text" class="form-control form-control-sm @error('nama.'.$i) is-invalid @enderror" name="nama[]" value="{{ old('nama.'.$i) }}" placeholder="Nama Anggota {{ $i }}">
                     @error('nama.'.$i)
                        <div class="invalid-feedback">{{ $message }}</div>
                     @enderror
                  </div>
                  <div class="col-md-3">
                     <input type="text" class="form-control form-control-sm @error('nim.'.$i) is-invalid @enderror" name="nim[]" value="{{ old('nim.'.$i) }}" placeholder="NIM">
                     @error('nim.'.$i)
                        <div class="invalid-feedback">{{ $message }}</div>
                     @enderror
                  </div>
                  <div class="col-md-3">
                     <select class="form-select form-select-sm @error('prodi.'.$i) is-invalid @enderror" name="prodi[]">
                        <option disabled selected>Pilih Prodi</option>
                        <option value="Fisika" {{ old('prodi.'.$i) == 'Fisika' ? 'selected' : '' }}>Fisika</option>
                        <option value="Matematika" {{ old('prodi.'.$i) == 'Matematika' ? 'selected' : '' }}>Matematika</option>
                        <option value="Informatika" {{ old('prodi.'.$i) == 'Informatika' ? 'selected' : '' }}>Informatika</option>
                        <option value="Sistem Informasi" {{ old('prodi.'.$i) == 'Sistem Informasi' ? 'selected' : '' }}>Sistem Informasi</option>
                        <option value="Ilmu Aktuaria" {{ old('prodi.'.$i) == 'Ilmu Aktuaria' ? 'selected' : '' }}>Ilmu Aktuaria</option>
                        <option value="Statistika" {{ old('prodi.'.$i) == 'Statistika' ? 'selected' : '' }}>Statistika</option>
                        <option value="Bisnis Digital" {{ old('prodi.'.$i) == 'Bisnis Digital' ? 'selected' : '' }}>Bisnis Digital</option>
                        <option value="Teknik Mesin" {{ old('prodi.'.$i) == 'Teknik Mesin' ? 'selected' : '' }}>Teknik Mesin</option>
                        <option value="Teknik Elektro" {{ old('prodi.'.$i) == 'Teknik Elektro' ? 'selected' : '' }}>Teknik Elektro</option>
                        <option value="Teknik Kimia" {{ old('prodi.'.$i) == 'Teknik Kimia' ? 'selected' : '' }}>Teknik Kimia</option>
                        <option value="Teknik Industri" {{ old('prodi.'.$i) == 'Teknik Industri' ? 'selected' : '' }}>Teknik Industri</option>
                        <option value="Teknik Material dan Metalurgi" {{ old('prodi.'.$i) == 'Teknik Material dan Metalurgi' ? 'selected' : '' }}>Teknik Material dan Metalurgi</option>
                        <option value="Rekayasa Keselamatan" {{ old('prodi.'.$i) == 'Rekayasa Keselamatan' ? 'selected' : '' }}>Rekayasa Keselamatan</option>
                        <option value="Teknik Logistik" {{ old('prodi.'.$i) == 'Teknik Logistik' ? 'selected' : '' }}>Teknik Logistik</option>
                        <option value="Teknik Sipil" {{ old('prodi.'.$i) == 'Teknik Sipil' ? 'selected' : '' }}>Teknik Sipil</option>
                        <option value="Perencanaan Wilayah dan Kota" {{ old('prodi.'.$i) == 'Perencanaan Wilayah dan Kota' ? 'selected' : '' }}>Perencanaan Wilayah dan Kota</option>
                        <option value="Arsitektur" {{ old('prodi.'.$i) == 'Arsitektur' ? 'selected' : '' }}>Arsitektur</option>
                        <option value="Desain Komunikasi Visual" {{ old('prodi.'.$i) == 'Desain Komunikasi Visual' ? 'selected' : '' }}>Desain Komunikasi Visual</option>
                        <option value="Teknik Lingkungan" {{ old('prodi.'.$i) == 'Teknik Lingkungan' ? 'selected' : '' }}>Teknik Lingkungan</option>
                        <option value="Teknik Perkapalan" {{ old('prodi.'.$i) == 'Teknik Perkapalan' ? 'selected' : '' }}>Teknik Perkapalan</option>
                        <option value="Teknik Kelautan" {{ old('prodi.'.$i) == 'Teknik Kelautan' ? 'selected' : '' }}>Teknik Kelautan</option>
                     </select>
                     @error('prodi.'.$i)
                        <div class="invalid-feedback">{{ $message }}</div>
                     @enderror
                  </div>
                  <div class="col-md-3">
                     <select class="form-select form-select-sm @error('peran.'.$i) is-invalid @enderror" name="peran[]">
                        <option disabled selected>Pilih Peran</option>
                        <option value="Ketua" {{ old('peran.'.$i) == 'Ketua' ? 'selected' : '' }}>Ketua</option>
                        <option value="Anggota" {{ old('peran.'.$i) == 'Anggota' ? 'selected' : '' }}>Anggota</option>
                     </select>
                     @error('peran.'.$i)
                        <div class="invalid-feedback">{{ $message }}</div>
                     @enderror
                  </div>
               </div>
               @endfor

               <!-- Upload File -->
               <div class="row mt-4">
                  <div class="col-md-12">
                     <label for="file" class="form-label">Upload Form Kesesuaian CPMK</label>
                     <input type="file" class="form-control @error('file') is-invalid @enderror" id="file" name="file" accept=".pdf" required>
                     <small class="text-muted">Hanya file PDF maksimal 10 MB yang diperbolehkan</small>
                     @error('file')
                        <div class="invalid-feedback">{{ $message }}</div>
                     @enderror
                  </div>
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

<!-- Modal Detail Pendaftar -->
@if(isset($pendaftar))
@foreach($pendaftar as $index => $item)
<div class="modal fade" id="detailModal{{ $item->id ?? $index }}" tabindex="-1" aria-labelledby="detailModalLabel{{ $item->id ?? $index }}" aria-hidden="true">
   <div class="modal-dialog modal-lg">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title" id="detailModalLabel{{ $item->id ?? $index }}">Detail Pendaftar</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <div class="modal-body">
            <div class="row">
               <div class="col-md-12 mb-3">
                  <strong>Judul Kegiatan:</strong>
                  <p>{{ $item->judul_kegiatan ?? 'Judul Kegiatan Sample' }}</p>
               </div>
               <div class="col-md-6 mb-3">
                  <strong>Mitra:</strong>
                  <p>{{ $item->mitra ?? 'Mitra A' }}</p>
               </div>
               <div class="col-md-6 mb-3">
                  <strong>Lokasi Mitra:</strong>
                  <p>{{ $item->lokasi_mitra ?? 'Lokasi Mitra' }}</p>
               </div>
               <div class="col-md-6 mb-3">
                  <strong>Jumlah Anggota:</strong>
                  <p>{{ $item->jumlah_anggota ?? '5' }} Orang</p>
               </div>
               <div class="col-md-6 mb-3">
                  <strong>Tanggal Daftar:</strong>
                  <p>{{ $item->tanggal_daftar ?? '2024-01-15' }}</p>
               </div>
               <div class="col-md-6 mb-3">
                  <strong>Status Verifikasi:</strong>
                  <p>
                     @if(isset($item->status_verifikasi))
                        @if($item->status_verifikasi == 'diterima')
                           <span class="badge bg-success">Diterima</span>
                        @elseif($item->status_verifikasi == 'ditolak')
                           <span class="badge bg-danger">Ditolak</span>
                        @elseif($item->status_verifikasi == 'pending')
                           <span class="badge bg-warning">Pending</span>
                        @endif
                     @else
                        <span class="badge bg-secondary">Belum Diverifikasi</span>
                     @endif
                  </p>
               </div>
               @if(isset($item->file_name) && $item->file_name)
               <div class="col-md-12 mb-3">
                  <strong>File CPMK:</strong>
                  <div class="d-flex gap-2 align-items-center flex-wrap">
                     <a href="{{ route('files.pdf.dashboard.download', $item->file_name) }}" class="btn btn-sm btn-primary shadow-sm" title="Download File">
                        <i class="fas fa-download me-1"></i>Download PDF
                     </a>
                     <button type="button" class="btn btn-sm btn-info text-white shadow-sm" onclick="openPdfViewer('{{ route('files.pdf.dashboard', $item->file_name) }}', '{{ $item->file_name }}')" title="Lihat PDF">
                        <i class="fas fa-eye me-1"></i>Lihat PDF
                     </button>
                     <span class="badge bg-light text-dark border">{{ $item->file_name }}</span>
                  </div>
               </div>
               @endif
            </div>

            <!-- Tabel Anggota -->
            @if(isset($item->anggota) && $item->anggota->count() > 0)
            <div class="row mt-4">
               <div class="col-md-12">
                  <h6 class="mb-3">Daftar Anggota Kelompok:</h6>
                  <div class="table-responsive">
                     <table class="table table-sm table-bordered">
                        <thead class="table-light">
                           <tr>
                              <th>No</th>
                              <th>Nama</th>
                              <th>NIM</th>
                              <th>Program Studi</th>
                              <th>Peran</th>
                           </tr>
                        </thead>
                        <tbody>
                           @foreach($item->anggota as $indexAnggota => $anggota)
                           <tr>
                              <td>{{ $indexAnggota + 1 }}</td>
                              <td>{{ $anggota->nama }}</td>
                              <td>{{ $anggota->nim }}</td>
                              <td>{{ $anggota->program_studi }}</td>
                              <td>
                                 <span class="badge {{ $anggota->peran == 'Ketua' ? 'bg-primary' : 'bg-secondary' }}">
                                    {{ $anggota->peran }}
                                 </span>
                              </td>
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
@endif

<!-- Modal Verifikasi Pendaftar -->
@if(isset($pendaftar))
@foreach($pendaftar as $index => $item)
<div class="modal fade" id="verifikasiModal{{ $item->id ?? $index }}" tabindex="-1" aria-labelledby="verifikasiModalLabel{{ $item->id ?? $index }}" aria-hidden="true">
   <div class="modal-dialog modal-lg">
      <div class="modal-content">
         <div class="modal-header bg-success text-white">
            <h5 class="modal-title" id="verifikasiModalLabel{{ $item->id ?? $index }}">
               <i class="fas fa-check-circle me-2"></i>Verifikasi Pendaftar
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <form action="{{ route('pendaftar.verifikasi', $item->id ?? $index) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
               <div class="alert alert-info">
                  <i class="fas fa-info-circle me-2"></i>
                  <strong>Informasi Pendaftar:</strong> Silakan verifikasi data pendaftar berikut ini.
               </div>
               
               <div class="row">
                  <div class="col-md-12 mb-3">
                     <label class="form-label fw-bold">Judul Kegiatan</label>
                     <p class="form-control-plaintext">{{ $item->judul_kegiatan ?? 'Judul Kegiatan Sample' }}</p>
                  </div>
                  <div class="col-md-6 mb-3">
                     <label class="form-label fw-bold">Mitra</label>
                     <p class="form-control-plaintext">{{ $item->mitra ?? 'Mitra A' }}</p>
                  </div>
                  <div class="col-md-6 mb-3">
                     <label class="form-label fw-bold">Lokasi Mitra</label>
                     <p class="form-control-plaintext">{{ $item->lokasi_mitra ?? 'Lokasi Mitra' }}</p>
                  </div>
                  <div class="col-md-6 mb-3">
                     <label class="form-label fw-bold">Jumlah Anggota</label>
                     <p class="form-control-plaintext">{{ $item->jumlah_anggota ?? '5' }} Orang</p>
                  </div>
                  <div class="col-md-6 mb-3">
                     <label class="form-label fw-bold">Tanggal Daftar</label>
                     <p class="form-control-plaintext">{{ $item->tanggal_daftar ?? '2024-01-15' }}</p>
                  </div>
                  
                  @if(isset($item->file_name) && $item->file_name)
                  <div class="col-md-12 mb-3">
                     <label class="form-label fw-bold">File CPMK:</label>
                     <div class="d-flex gap-2 align-items-center flex-wrap">
                        <a href="{{ route('files.pdf.dashboard.download', $item->file_name) }}" class="btn btn-sm btn-primary shadow-sm" title="Download File">
                           <i class="fas fa-download me-1"></i>Download PDF
                        </a>
                        <button type="button" class="btn btn-sm btn-info text-white shadow-sm" onclick="openPdfViewer('{{ route('files.pdf.dashboard', $item->file_name) }}', '{{ $item->file_name }}')" title="Lihat PDF">
                           <i class="fas fa-eye me-1"></i>Lihat PDF
                        </button>
                        <span class="badge bg-light text-dark border">{{ $item->file_name }}</span>
                     </div>
                  </div>
                  @endif
                  
                  <div class="col-md-12 mb-3">
                     <label for="status_verifikasi_{{ $item->id ?? $index }}" class="form-label fw-bold">Status Verifikasi</label>
                     <select class="form-select" id="status_verifikasi_{{ $item->id ?? $index }}" name="status_verifikasi" required>
                        <option value="">Pilih Status</option>
                        <option value="diterima" {{ isset($item->status_verifikasi) && $item->status_verifikasi == 'diterima' ? 'selected' : '' }}>Diterima</option>
                        <option value="ditolak" {{ isset($item->status_verifikasi) && $item->status_verifikasi == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                        <option value="pending" {{ isset($item->status_verifikasi) && $item->status_verifikasi == 'pending' ? 'selected' : '' }}>Pending</option>
                     </select>
                  </div>
                  
                  <div class="col-md-12 mb-3">
                     <label for="catatan_verifikasi_{{ $item->id ?? $index }}" class="form-label fw-bold">Catatan Verifikasi</label>
                     <textarea class="form-control" id="catatan_verifikasi_{{ $item->id ?? $index }}" name="catatan_verifikasi" rows="3" placeholder="Masukkan catatan verifikasi (opsional)">{{ $item->catatan_verifikasi ?? '' }}</textarea>
                  </div>
               </div>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                  <i class="fas fa-times me-1"></i>Batal
               </button>
               <button type="submit" class="btn btn-success">
                  <i class="fas fa-check me-1"></i>Verifikasi
               </button>
            </div>
         </form>
      </div>
   </div>
</div>
@endforeach
@endif

<!-- Modal Edit Pendaftar -->
@if(isset($pendaftar))
@foreach($pendaftar as $index => $item)
<div class="modal fade" id="editModal{{ $item->id ?? $index }}" tabindex="-1" aria-labelledby="editModalLabel{{ $item->id ?? $index }}" aria-hidden="true">
   <div class="modal-dialog modal-lg">
      <div class="modal-content">
         <div class="modal-header bg-warning text-dark">
            <h5 class="modal-title" id="editModalLabel{{ $item->id ?? $index }}">
               <i class="fas fa-edit me-2"></i>Edit Pendaftar
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <form action="{{ route('pendaftar.update', $item->id ?? $index) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
               <div class="row">
                  <div class="col-md-12 mb-3">
                     <label for="edit_judul_kegiatan_{{ $item->id ?? $index }}" class="form-label">Judul Kegiatan</label>
                     <input type="text" class="form-control @error('judul_kegiatan') is-invalid @enderror" id="edit_judul_kegiatan_{{ $item->id ?? $index }}" name="judul_kegiatan" value="{{ old('judul_kegiatan', $item->judul_kegiatan ?? 'Judul Kegiatan Sample') }}" required>
                     @error('judul_kegiatan')
                        <div class="invalid-feedback">{{ $message }}</div>
                     @enderror
                  </div>
                  <div class="col-md-6 mb-3">
                     <label for="edit_mitra_{{ $item->id ?? $index }}" class="form-label">Mitra</label>
                     <input type="text" class="form-control @error('mitra') is-invalid @enderror" id="edit_mitra_{{ $item->id ?? $index }}" name="mitra" value="{{ old('mitra', $item->mitra ?? 'Mitra A') }}" required>
                     @error('mitra')
                        <div class="invalid-feedback">{{ $message }}</div>
                     @enderror
                  </div>
                  <div class="col-md-6 mb-3">
                     <label for="edit_jumlah_anggota_{{ $item->id ?? $index }}" class="form-label">Jumlah Anggota</label>
                     <input type="number" class="form-control @error('jumlah_anggota') is-invalid @enderror" id="edit_jumlah_anggota_{{ $item->id ?? $index }}" name="jumlah_anggota" value="{{ old('jumlah_anggota', $item->jumlah_anggota ?? '5') }}" min="1" required>
                     @error('jumlah_anggota')
                        <div class="invalid-feedback">{{ $message }}</div>
                     @enderror
                  </div>
                  <div class="col-md-12 mb-3">
                     <label for="edit_deskripsi_{{ $item->id ?? $index }}" class="form-label">Deskripsi Kegiatan</label>
                     <textarea class="form-control @error('deskripsi') is-invalid @enderror" id="edit_deskripsi_{{ $item->id ?? $index }}" name="deskripsi" rows="3">{{ old('deskripsi', $item->deskripsi ?? 'Deskripsi kegiatan singkat') }}</textarea>
                     @error('deskripsi')
                        <div class="invalid-feedback">{{ $message }}</div>
                     @enderror
                  </div>
               </div>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                  <i class="fas fa-times me-1"></i>Batal
               </button>
               <button type="submit" class="btn btn-warning">
                  <i class="fas fa-save me-1"></i>Update
               </button>
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

.shadow-sm {
   box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
}

@media (max-width: 768px) {
   .modal-fullscreen {
      max-width: 100vw !important;
      width: 100vw !important;
      margin: 0 !important;
   }
   
   #pdfViewer {
      height: calc(100vh - 100px);
   }
   
   .pdf-viewer-header {
      padding: 0.75rem 1rem;
   }
   
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
   console.log('Pendaftar page loaded');
   
   // Debug: Log semua form delete
   const deleteForms = document.querySelectorAll('form[action*="pendaftar/destroy"]');
   console.log('Found delete forms:', deleteForms.length);
   
   deleteForms.forEach((form, index) => {
      console.log(`Delete form ${index + 1}:`, form);
   });

   // Validasi form pendaftar
   const pendaftarForm = document.getElementById('tambahPendaftarModal');
   if (pendaftarForm) {
      const form = pendaftarForm.querySelector('form');
      const fileInput = form.querySelector('input[name="file"]');
      
      form.addEventListener('submit', function(e) {
         // Validasi Prodi - minimal 2 prodi berbeda
         const selectedProdi = Array.from(form.querySelectorAll('select[name="prodi[]"]'))
            .map(el => el.value)
            .filter(val => val && val !== 'Pilih Prodi');
         const uniqueProdi = [...new Set(selectedProdi)];

         if (uniqueProdi.length < 2) {
            e.preventDefault();
            alert('Minimal harus ada 2 macam Program Studi yang berbeda dalam kelompok!');
            return;
         }

         // Validasi file
         if (fileInput.files.length === 0) {
            e.preventDefault();
            alert('File CPMK wajib diupload!');
            return;
         }

         const file = fileInput.files[0];
         if (file.type !== 'application/pdf' || file.size > 10 * 1024 * 1024) {
            e.preventDefault();
            alert('Hanya file PDF maksimal 10 MB yang diperbolehkan!');
            return;
         }
      });

      // File validation
      fileInput.addEventListener('change', function() {
         if (fileInput.files.length > 0) {
            const file = fileInput.files[0];
            if (file.type !== 'application/pdf' || file.size > 10 * 1024 * 1024) {
               alert('Hanya file PDF maksimal 10 MB yang diperbolehkan!');
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

