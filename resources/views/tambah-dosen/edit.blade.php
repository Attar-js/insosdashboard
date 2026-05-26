<x-app-layout :assets="$assets ?? []">
<div class="row">
   <div class="col-lg-12">
      <div class="card rounded">
         <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
               <h4 class="card-title mb-0">Edit Data Dosen</h4>
               <div class="card-tools">
                  <a href="{{ route('tambah-dosen.index') }}" class="btn btn-secondary btn-sm">
                     <i class="fas fa-arrow-left"></i> Kembali
                  </a>
               </div>
            </div>
         </div>
         <div class="card-body">
            <form action="{{ route('tambah-dosen.update', $dosen->id) }}" method="POST">
               @csrf
               @method('PUT')
               
               <!-- Nama Dosen -->
               <div class="row">
                  <div class="col-md-12 mb-3">
                     <label for="name" class="form-label">Nama Lengkap Dosen <span class="text-danger">*</span></label>
                     <input type="text" class="form-control @error('name') is-invalid @enderror" 
                            id="name" name="name" value="{{ old('name', $dosen->name) }}" 
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
                            id="email" name="email" value="{{ old('email', $dosen->email) }}" 
                            placeholder="dosen@email.com" required>
                     @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                     @enderror
                  </div>
                  <div class="col-md-6 mb-3">
                     <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                     <input type="text" class="form-control @error('username') is-invalid @enderror" 
                            id="username" name="username" value="{{ old('username', $dosen->username) }}" 
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
                            id="nip" name="nip" value="{{ old('nip', $dosen->nip) }}" 
                            placeholder="199208012019031010" required>
                     @error('nip')
                        <div class="invalid-feedback">{{ $message }}</div>
                     @enderror
                  </div>
                  <div class="col-md-6 mb-3">
                     <label for="phone_number" class="form-label">No. Telepon</label>
                     <input type="text" class="form-control @error('phone_number') is-invalid @enderror" 
                            id="phone_number" name="phone_number" value="{{ old('phone_number', $dosen->phone_number) }}" 
                            placeholder="081234567890">
                     @error('phone_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                     @enderror
                  </div>
               </div>

               <!-- Password (Opsional) -->
               <div class="row">
                  <div class="col-md-6 mb-3">
                     <label for="password" class="form-label">Password Baru (Opsional)</label>
                     <input type="password" class="form-control @error('password') is-invalid @enderror" 
                            id="password" name="password" placeholder="Kosongkan jika tidak ingin mengubah">
                     @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                     @enderror
                  </div>
                  <div class="col-md-6 mb-3">
                     <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                     <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" 
                            id="password_confirmation" name="password_confirmation" placeholder="Ulangi password baru">
                     @error('password_confirmation')
                        <div class="invalid-feedback">{{ $message }}</div>
                     @enderror
                  </div>
               </div>

               <div class="row">
                  <div class="col-12">
                     <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Update Dosen
                     </button>
                     <a href="{{ route('tambah-dosen.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i>Batal
                     </a>
                  </div>
               </div>
            </form>
         </div>
      </div>
   </div>
</div>
</x-app-layout> 
