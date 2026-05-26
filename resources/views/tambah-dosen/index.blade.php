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
               <h4 class="card-title mb-0">Data Dosen</h4>
               <div class="card-tools">
                  <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#tambahDosenModal">
                     <i class="fas fa-plus"></i> Tambah Dosen
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
                        <th scope="col" width="25%">Nama Dosen</th>
                        <th scope="col" width="18%">Email</th>
                        <th scope="col" width="15%">Username</th>
                        <th scope="col" width="18%">NIP</th>
                        <th scope="col" width="15%">No. Telepon</th>
                        <th scope="col" width="14%">Aksi</th>
                     </tr>
                  </thead>
                  <tbody>
                     @forelse($dosenList ?? [] as $index => $dosen)
                     <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                           <h6 class="mb-0">{{ $dosen->name }}</h6>
                           <small class="text-muted">ID: {{ $dosen->id }}</small>
                        </td>
                        <td>
                           <span class="badge bg-info">{{ $dosen->email }}</span>
                        </td>
                        <td>
                           <small class="text-muted">{{ $dosen->username }}</small>
                        </td>
                        <td>
                           <span class="badge bg-secondary">{{ $dosen->nip }}</span>
                        </td>
                        <td>
                           <small class="text-muted">{{ $dosen->phone_number ?? 'N/A' }}</small>
                        </td>
                        <td>
                           <div class="d-flex gap-2">
                              <button type="button" class="btn btn-sm btn-info text-white shadow-sm" 
                                      onclick="editDosen('{{ $dosen->id }}', '{{ $dosen->name }}', '{{ $dosen->email }}', '{{ $dosen->username }}', '{{ $dosen->nip }}', '{{ $dosen->phone_number }}')" 
                                      title="Edit Dosen">
                                 <i class="fas fa-edit me-1"></i>Edit
                              </button>
                              <button type="button" class="btn btn-sm btn-danger shadow-sm" 
                                      onclick="hapusDosen('{{ $dosen->id }}', '{{ $dosen->name }}')" 
                                      title="Hapus Dosen">
                                 <i class="fas fa-trash me-1"></i>Hapus
                              </button>
                           </div>
                        </td>
                     </tr>
                     @empty
                     <tr>
                        <td colspan="7" class="text-center py-4">
                           <i class="fas fa-users fa-2x text-muted mb-2"></i>
                           <h6 class="text-muted">Belum ada data dosen</h6>
                           <p class="text-muted mb-0">Klik tombol "Tambah Dosen" untuk menambahkan dosen baru</p>
                        </td>
                     </tr>
                     @endforelse
                  </tbody>
               </table>
            </div>
         </div>
      </div>
   </div>
</div>

<!-- Modal Tambah Dosen -->
<div class="modal fade" id="tambahDosenModal" tabindex="-1" aria-labelledby="tambahDosenModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-lg">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title" id="tambahDosenModalLabel">Tambah Dosen Baru</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <form action="{{ route('tambah-dosen.store') }}" method="POST">
            @csrf
            <div class="modal-body">
               <!-- Nama Dosen -->
               <div class="row">
                  <div class="col-md-12 mb-3">
                     <label for="name" class="form-label">Nama Lengkap Dosen <span class="text-danger">*</span></label>
                     <input type="text" class="form-control @error('name') is-invalid @enderror" 
                            id="name" name="name" value="{{ old('name') }}" 
                            placeholder="Contoh: Dr. Ahmad Fauzi, S.T., M.T." required>
                     @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                     @enderror
                  </div>
               </div>

               <!-- Email dan Username -->
               <div class="row">
                  <div class="col-md-6 mb-3">
                     <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                     <input type="email" class="form-control @error('email') is-invalid @enderror" 
                            id="email" name="email" value="{{ old('email') }}" 
                            placeholder="dosen@email.com" required>
                     @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                     @enderror
                  </div>
                  <div class="col-md-6 mb-3">
                     <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                     <input type="text" class="form-control @error('username') is-invalid @enderror" 
                            id="username" name="username" value="{{ old('username') }}" 
                            placeholder="username_dosen" required>
                     @error('username')
                        <div class="invalid-feedback">{{ $message }}</div>
                     @enderror
                  </div>
               </div>

               <!-- NIP dan No. Telepon -->
               <div class="row">
                  <div class="col-md-6 mb-3">
                     <label for="nip" class="form-label">NIP <span class="text-danger">*</span></label>
                     <input type="text" class="form-control @error('nip') is-invalid @enderror" 
                            id="nip" name="nip" value="{{ old('nip') }}" 
                            placeholder="199208012019031010" required>
                     @error('nip')
                        <div class="invalid-feedback">{{ $message }}</div>
                     @enderror
                  </div>
                  <div class="col-md-6 mb-3">
                     <label for="phone_number" class="form-label">No. Telepon</label>
                     <input type="text" class="form-control @error('phone_number') is-invalid @enderror" 
                            id="phone_number" name="phone_number" value="{{ old('phone_number') }}" 
                            placeholder="081234567890">
                     @error('phone_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                     @enderror
                  </div>
               </div>

               <!-- Password -->
               <div class="row">
                  <div class="col-md-6 mb-3">
                     <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                     <input type="password" class="form-control @error('password') is-invalid @enderror" 
                            id="password" name="password" placeholder="Minimal 8 karakter" required>
                     @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                     @enderror
                  </div>
                  <div class="col-md-6 mb-3">
                     <label for="password_confirmation" class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                     <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" 
                            id="password_confirmation" name="password_confirmation" placeholder="Ulangi password" required>
                     @error('password_confirmation')
                        <div class="invalid-feedback">{{ $message }}</div>
                     @enderror
                  </div>
               </div>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
               <button type="submit" class="btn btn-primary">
                  <i class="fas fa-save me-1"></i>Simpan Dosen
               </button>
            </div>
         </form>
      </div>
   </div>
</div>

<!-- Modal Edit Dosen -->
<div class="modal fade" id="editDosenModal" tabindex="-1" aria-labelledby="editDosenModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-lg">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title" id="editDosenModalLabel">Edit Data Dosen</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <form id="editDosenForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
               <!-- Nama Dosen -->
               <div class="row">
                  <div class="col-md-12 mb-3">
                     <label for="edit_name" class="form-label">Nama Lengkap Dosen <span class="text-danger">*</span></label>
                     <input type="text" class="form-control" id="edit_name" name="name" required>
                  </div>
               </div>

               <!-- Email dan Username -->
               <div class="row">
                  <div class="col-md-6 mb-3">
                     <label for="edit_email" class="form-label">Email <span class="text-danger">*</span></label>
                     <input type="email" class="form-control" id="edit_email" name="email" required>
                  </div>
                  <div class="col-md-6 mb-3">
                     <label for="edit_username" class="form-label">Username <span class="text-danger">*</span></label>
                     <input type="text" class="form-control" id="edit_username" name="username" required>
                  </div>
               </div>

               <!-- NIP dan No. Telepon -->
               <div class="row">
                  <div class="col-md-6 mb-3">
                     <label for="edit_nip" class="form-label">NIP <span class="text-danger">*</span></label>
                     <input type="text" class="form-control" id="edit_nip" name="nip" required>
                  </div>
                  <div class="col-md-6 mb-3">
                     <label for="edit_phone_number" class="form-label">No. Telepon</label>
                     <input type="text" class="form-control" id="edit_phone_number" name="phone_number">
                  </div>
               </div>

               <!-- Password (Opsional) -->
               <div class="row">
                  <div class="col-md-6 mb-3">
                     <label for="edit_password" class="form-label">Password Baru (Opsional)</label>
                     <input type="password" class="form-control" id="edit_password" name="password" placeholder="Kosongkan jika tidak ingin mengubah">
                  </div>
                  <div class="col-md-6 mb-3">
                     <label for="edit_password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                     <input type="password" class="form-control" id="edit_password_confirmation" name="password_confirmation" placeholder="Ulangi password baru">
                  </div>
               </div>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
               <button type="submit" class="btn btn-primary">
                  <i class="fas fa-save me-1"></i>Update Dosen
               </button>
            </div>
         </form>
      </div>
   </div>
</div>

<!-- Modal Konfirmasi Hapus -->
<div class="modal fade" id="hapusDosenModal" tabindex="-1" aria-labelledby="hapusDosenModalLabel" aria-hidden="true">
   <div class="modal-dialog">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title" id="hapusDosenModalLabel">Konfirmasi Hapus Dosen</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <div class="modal-body">
            <p>Apakah Anda yakin ingin menghapus dosen <strong id="hapusDosenNama"></strong>?</p>
            <p class="text-danger">Tindakan ini tidak dapat dibatalkan!</p>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <form id="hapusDosenForm" method="POST" style="display: inline;">
               @csrf
               @method('DELETE')
               <button type="submit" class="btn btn-danger">
                  <i class="fas fa-trash me-1"></i>Hapus Dosen
               </button>
            </form>
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
});

function editDosen(id, name, email, username, nip, phoneNumber) {
    // Set form action
    $('#editDosenForm').attr('action', '{{ route("tambah-dosen.update", "") }}/' + id);
    
    // Set form values
    $('#edit_name').val(name);
    $('#edit_email').val(email);
    $('#edit_username').val(username);
    $('#edit_nip').val(nip);
    $('#edit_phone_number').val(phoneNumber);
    
    // Show modal
    $('#editDosenModal').modal('show');
}

function hapusDosen(id, name) {
    // Set form action
    $('#hapusDosenForm').attr('action', '{{ route("tambah-dosen.destroy", "") }}/' + id);
    
    // Set nama dosen
    $('#hapusDosenNama').text(name);
    
    // Show modal
    $('#hapusDosenModal').modal('show');
}
</script>
</x-app-layout> 
