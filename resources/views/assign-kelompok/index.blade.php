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
               <h4 class="card-title mb-0">Data Kelompok Siap Assign</h4>
               <div class="card-tools">
                  <a href="{{ route('assign-kelompok.assigned-groups') }}" class="btn btn-info btn-sm me-2">
                     <i class="fas fa-list me-1"></i>Kelompok Diassign
                  </a>
                  <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#assignKelompokModal">
                     <i class="fas fa-user-plus"></i> Assign Kelompok
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
                        <th scope="col" width="20%">Nama Kelompok</th>
                        <th scope="col" width="25%">Judul Kegiatan</th>
                        <th scope="col" width="12%">Mitra</th>
                        <th scope="col" width="8%">Anggota</th>
                        <th scope="col" width="8%">Progress</th>
                        <th scope="col" width="8%">Status</th>
                        <th scope="col" width="14%">Aksi</th>
                     </tr>
                  </thead>
                  <tbody>
                     @forelse($verifiedGroups ?? [] as $index => $group)
                     <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                           <h6 class="mb-0">{{ $group['nama_kelompok'] }}</h6>
                           <small class="text-muted">ID: {{ $group['id'] }}</small>
                        </td>
                        <td>
                           <h6 class="mb-0">{{ $group['judul_kegiatan'] }}</h6>
                        </td>
                        <td>
                           <span class="badge bg-info">{{ $group['nama_mitra'] ?? 'N/A' }}</span>
                        </td>
                        <td class="text-center">
                           <span class="badge bg-success">{{ count($group['members']) }} Orang</span>
                        </td>
                        <td>
                           <div class="progress" style="height: 20px;">
                              <div class="progress-bar bg-success" role="progressbar" 
                                   style="width: {{ $group['progress_verifikasi'] }}%"
                                   aria-valuenow="{{ $group['progress_verifikasi'] }}" 
                                   aria-valuemin="0" aria-valuemax="100">
                                 {{ $group['progress_verifikasi'] }}%
                              </div>
                           </div>
                        </td>
                        <td>
                           @if($group['status'] === 'assigned')
                              <span class="badge bg-success">
                                 <i class="fas fa-check-circle me-1"></i>Sudah Diassign
                              </span>
                              @if($group['dosen_name'])
                                 <br><small class="text-muted">Dosen: {{ $group['dosen_name'] }}</small>
                                 @if($group['assigned_at'])
                                    <br><small class="text-muted">{{ \Carbon\Carbon::parse($group['assigned_at'])->format('d/m/Y H:i') }}</small>
                                 @endif
                              @endif
                           @else
                              <span class="badge bg-warning">
                                 <i class="fas fa-clock me-1"></i>Belum Diassign
                              </span>
                           @endif
                        </td>
                        <td>
                           <div class="d-flex gap-2">
                              @if($group['status'] === 'assigned')
                                 <button type="button" class="btn btn-sm btn-success shadow-sm" disabled title="Sudah Diassign">
                                    <i class="fas fa-check me-1"></i>Sudah Assign
                                 </button>
                                 <button type="button" class="btn btn-sm btn-info text-white shadow-sm" 
                                         onclick="detailKelompok('{{ $group['id'] }}')" 
                                         title="Detail Kelompok">
                                    <i class="fas fa-eye me-1"></i>Detail
                                 </button>
                                 <button type="button" class="btn btn-sm btn-danger shadow-sm" 
                                         onclick="hapusAssignment('{{ $group['id'] }}', '{{ $group['nama_kelompok'] }}')" 
                                         title="Hapus Assignment">
                                    <i class="fas fa-trash me-1"></i>Hapus
                                 </button>
                              @else
                                 <button type="button" class="btn btn-sm btn-primary shadow-sm" 
                                         onclick="assignKelompok('{{ $group['id'] }}', '{{ $group['nama_kelompok'] }}')" 
                                         title="Assign Kelompok">
                                    <i class="fas fa-user-plus me-1"></i>Assign
                                 </button>
                                 <button type="button" class="btn btn-sm btn-info text-white shadow-sm" 
                                         onclick="detailKelompok('{{ $group['id'] }}')" 
                                         title="Detail Kelompok">
                                    <i class="fas fa-eye me-1"></i>Detail
                                 </button>
                              @endif
                           </div>
                        </td>
                     </tr>
                     @empty
                     <tr>
                        <td colspan="8" class="text-center py-4">
                           <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                           <h6 class="text-muted">Belum ada kelompok yang siap diassign</h6>
                           <p class="text-muted mb-0">Kelompok harus memiliki progress verifikasi 100% untuk dapat diassign</p>
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

<!-- Modal Assign Kelompok -->
<div class="modal fade" id="assignKelompokModal" tabindex="-1" aria-labelledby="assignKelompokModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-xl">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title" id="assignKelompokModalLabel">Assign Kelompok ke Dosen</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <form id="assignForm" method="POST" action="/assign-kelompok/assign/placeholder">
            @csrf
            <div class="modal-body">
               <!-- Pilih Kelompok -->
               <div class="row">
                  <div class="col-md-12 mb-3">
                     <label for="group_id" class="form-label">Pilih Kelompok <span class="text-danger">*</span></label>
                     <input type="text" class="form-control mb-2" id="search_group_id" placeholder="Ketik untuk mencari kelompok (nama/judul)">
                     <select class="form-select @error('group_id') is-invalid @enderror" id="group_id" name="group_id" required>
                        <option value="">Pilih Kelompok</option>
                        @foreach($verifiedGroups ?? [] as $group)
                           <option value="{{ $group['id'] }}" 
                                   data-nama="{{ $group['nama_kelompok'] }}"
                                   data-judul="{{ $group['judul_kegiatan'] }}"
                                   data-lokasi="{{ $group['lokasi_kkn'] }}"
                                   data-mitra="{{ $group['nama_mitra'] ?? '' }}"
                                   data-lokasi-mitra="{{ $group['lokasi_mitra'] ?? '' }}"
                                   data-members="{{ json_encode($group['members']) }}">
                              {{ $group['nama_kelompok'] }} - {{ $group['judul_kegiatan'] }}
                           </option>
                        @endforeach
                     </select>
                     @error('group_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                     @enderror
                  </div>
               </div>

               <!-- Pilih Dosen -->
               <div class="row">
                  <div class="col-md-12 mb-3">
                     <label for="dosen_id" class="form-label">Pilih Dosen Pembimbing <span class="text-danger">*</span></label>
                     <select class="form-select @error('dosen_id') is-invalid @enderror" id="dosen_id" name="dosen_id" required>
                        <option value="">Pilih Dosen Pembimbing</option>
                        @foreach($dosenList ?? [] as $dosen)
                           <option value="{{ $dosen['id'] }}" 
                                   data-nama="{{ $dosen['name'] }}"
                                   data-nip="{{ $dosen['nip'] }}">
                              {{ $dosen['name'] }} - {{ $dosen['nip'] }}
                           </option>
                        @endforeach
                     </select>
                     @error('dosen_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                     @enderror
                  </div>
               </div>

               <!-- Informasi Kelompok -->
               <div class="row" id="infoKelompok" style="display: none;">
                  <div class="col-md-12">
                     <div class="card bg-light">
                        <div class="card-header">
                           <h6 class="card-title mb-0">
                              <i class="fas fa-info-circle me-2"></i>Informasi Kelompok
                           </h6>
                        </div>
                        <div class="card-body">
                           <div class="row">
                              <div class="col-md-6">
                                 <table class="table table-sm">
                                    <tr>
                                       <td width="30%"><strong>Nama Kelompok:</strong></td>
                                       <td id="info-nama-kelompok">-</td>
                                    </tr>
                                    <tr>
                                       <td><strong>Judul Kegiatan:</strong></td>
                                       <td id="info-judul-kegiatan">-</td>
                                    </tr>
                                    <tr>
                                       <td><strong>Nama Mitra:</strong></td>
                                       <td id="info-nama-mitra">-</td>
                                    </tr>
                                    <tr>
                                       <td><strong>Lokasi Mitra:</strong></td>
                                       <td id="info-lokasi-mitra">-</td>
                                    </tr>
                                 </table>
                              </div>
                              <div class="col-md-6">
                                 <h6>Anggota Kelompok:</h6>
                                 <div id="info-anggota">
                                    <p class="text-muted">Pilih kelompok untuk melihat anggota</p>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>

               <!-- Catatan Assignment -->
               <div class="row">
                  <div class="col-md-12 mb-3">
                     <label for="assignment_note" class="form-label">Catatan Assignment</label>
                     <textarea class="form-control @error('assignment_note') is-invalid @enderror" 
                               id="assignment_note" name="assignment_note" rows="4" 
                               placeholder="Catatan tambahan untuk assignment ini (opsional)">{{ old('assignment_note') }}</textarea>
                     @error('assignment_note')
                        <div class="invalid-feedback">{{ $message }}</div>
                     @enderror
                  </div>
               </div>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
               <button type="submit" class="btn btn-primary">
                  <i class="fas fa-save me-1"></i>Assign Kelompok
               </button>
            </div>
         </form>
      </div>
   </div>
</div>

<!-- Modal Detail Kelompok -->
<div class="modal fade" id="detailKelompokModal" tabindex="-1" aria-labelledby="detailKelompokModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-lg">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title" id="detailKelompokModalLabel">Detail Kelompok</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <div class="modal-body" id="detailKelompokContent">
            <!-- Content will be loaded dynamically -->
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
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

    // Handle group selection
    $('#group_id').on('change', function() {
        var selectedOption = $(this).find('option:selected');
        if (selectedOption.val()) {
            // Update form action
            var newAction = '/assign-kelompok/assign/' + selectedOption.val();
            $('#assignForm').attr('action', newAction);
            console.log('Form action updated to:', newAction);
            
            // Show info section
            $('#infoKelompok').show();
            
            // Update info
            $('#info-nama-kelompok').text(selectedOption.data('nama'));
            $('#info-judul-kegiatan').text(selectedOption.data('judul'));
            $('#info-nama-mitra').text(selectedOption.data('mitra') || 'N/A');
            $('#info-lokasi-mitra').text(selectedOption.data('lokasi-mitra') || 'N/A');
            
            // Update anggota
            var members = selectedOption.data('members');
            var anggotaHtml = '';
            if (members && members.length > 0) {
                anggotaHtml += '<div class="table-responsive">';
                anggotaHtml += '<table class="table table-sm table-bordered">';
                anggotaHtml += '<thead class="table-light">';
                anggotaHtml += '<tr>';
                anggotaHtml += '<th>No</th>';
                anggotaHtml += '<th>Nama</th>';
                anggotaHtml += '<th>NIM</th>';
                anggotaHtml += '<th>Peran</th>';
                anggotaHtml += '</tr>';
                anggotaHtml += '</thead>';
                anggotaHtml += '<tbody>';
                
                members.forEach(function(member, index) {
                    anggotaHtml += '<tr>';
                    anggotaHtml += '<td>' + (index + 1) + '</td>';
                    anggotaHtml += '<td>' + member.name + '</td>';
                    anggotaHtml += '<td>' + member.nim + '</td>';
                    anggotaHtml += '<td>';
                    if (member.role === 'Ketua') {
                        anggotaHtml += '<span class="badge bg-primary">' + member.role + '</span>';
                    } else {
                        anggotaHtml += '<span class="badge bg-secondary">' + member.role + '</span>';
                    }
                    anggotaHtml += '</td>';
                    anggotaHtml += '</tr>';
                });
                
                anggotaHtml += '</tbody>';
                anggotaHtml += '</table>';
                anggotaHtml += '</div>';
            } else {
                anggotaHtml = '<div class="alert alert-info">';
                anggotaHtml += '<i class="fas fa-info-circle me-2"></i>Tidak ada data anggota';
                anggotaHtml += '</div>';
            }
            $('#info-anggota').html(anggotaHtml);
        } else {
            $('#infoKelompok').hide();
        }
    });

    // Handle form submit
    $('#assignForm').on('submit', function(e) {
        var groupId = $('#group_id').val();
        var dosenId = $('#dosen_id').val();
        
        if (!groupId) {
            alert('Silakan pilih kelompok terlebih dahulu');
            e.preventDefault();
            return false;
        }
        
        if (!dosenId) {
            alert('Silakan pilih dosen terlebih dahulu');
            e.preventDefault();
            return false;
        }
        
        // Update form action one more time before submit
        var newAction = '/assign-kelompok/assign/' + groupId;
        $(this).attr('action', newAction);
        
        console.log('Submitting form to:', newAction);
        console.log('Group ID:', groupId);
        console.log('Dosen ID:', dosenId);
        
        return true;
    });

    // Simple keyword filter for group select
    $('#search_group_id').on('input', function() {
        var keyword = $(this).val().toLowerCase();
        $('#group_id option').each(function() {
            if (!$(this).val()) { $(this).show(); return; }
            var text = $(this).text().toLowerCase();
            $(this).toggle(text.indexOf(keyword) !== -1);
        });
    });

});


function assignKelompok(groupId, groupName) {
    // Set the group in modal
    $('#group_id').val(groupId).trigger('change');
    
    // Show modal
    $('#assignKelompokModal').modal('show');
}

function detailKelompok(groupId) {
    // Find the group data
    var group = null;
    @foreach($verifiedGroups ?? [] as $group)
        if ('{{ $group["id"] }}' === groupId) {
            group = @json($group);
        }
    @endforeach
    
    if (group) {
        var content = '<div class="row">';
        
        // Informasi Kelompok
        content += '<div class="col-md-12 mb-3">';
        content += '<strong>Judul Kegiatan:</strong>';
        content += '<p>' + group.judul_kegiatan + '</p>';
        content += '</div>';
        
        content += '<div class="col-md-6 mb-3">';
        content += '<strong>Nama Kelompok:</strong>';
        content += '<p>' + group.nama_kelompok + '</p>';
        content += '</div>';
        
        content += '<div class="col-md-6 mb-3">';
        content += '<strong>Nama Mitra:</strong>';
        content += '<p>' + (group.nama_mitra || 'N/A') + '</p>';
        content += '</div>';
        
        content += '<div class="col-md-6 mb-3">';
        content += '<strong>Lokasi Mitra:</strong>';
        content += '<p>' + (group.lokasi_mitra || 'N/A') + '</p>';
        content += '</div>';
        
        content += '<div class="col-md-6 mb-3">';
        content += '<strong>Progress Verifikasi:</strong>';
        content += '<p><span class="badge bg-success">' + group.progress_verifikasi + '%</span></p>';
        content += '</div>';
        
        content += '<div class="col-md-6 mb-3">';
        content += '<strong>Status Assignment:</strong>';
        if (group.status === 'assigned') {
            content += '<p><span class="badge bg-success">Sudah Diassign</span></p>';
            if (group.dosen_name) {
                content += '<small class="text-muted">Dosen: ' + group.dosen_name + '</small><br>';
            }
            if (group.assigned_at) {
                content += '<small class="text-muted">Assigned: ' + new Date(group.assigned_at).toLocaleDateString('id-ID') + '</small>';
            }
        } else {
            content += '<p><span class="badge bg-warning">Belum Diassign</span></p>';
        }
        content += '</div>';
        
        content += '</div>';
        
        // Tabel Anggota
        if (group.members && group.members.length > 0) {
            content += '<div class="row mt-4">';
            content += '<div class="col-md-12">';
            content += '<h6 class="mb-3">Daftar Anggota Kelompok:</h6>';
            content += '<div class="table-responsive">';
            content += '<table class="table table-sm table-bordered">';
            content += '<thead class="table-light">';
            content += '<tr>';
            content += '<th>No</th>';
            content += '<th>Nama</th>';
            content += '<th>NIM</th>';
            content += '<th>Peran</th>';
            content += '</tr>';
            content += '</thead>';
            content += '<tbody>';
            
            group.members.forEach(function(member, index) {
                content += '<tr>';
                content += '<td>' + (index + 1) + '</td>';
                content += '<td>' + member.name + '</td>';
                content += '<td>' + member.nim + '</td>';
                content += '<td>';
                if (member.role === 'Ketua') {
                    content += '<span class="badge bg-primary">' + member.role + '</span>';
                } else {
                    content += '<span class="badge bg-secondary">' + member.role + '</span>';
                }
                content += '</td>';
                content += '</tr>';
            });
            
            content += '</tbody>';
            content += '</table>';
            content += '</div>';
            content += '</div>';
            content += '</div>';
        } else {
            content += '<div class="row mt-4">';
            content += '<div class="col-md-12">';
            content += '<div class="alert alert-info">';
            content += '<i class="fas fa-info-circle me-2"></i>Tidak ada data anggota kelompok';
            content += '</div>';
            content += '</div>';
            content += '</div>';
        }
        
        $('#detailKelompokContent').html(content);
        $('#detailKelompokModal').modal('show');
    } else {
        alert('Data kelompok tidak ditemukan');
    }
}

function hapusAssignment(groupId, groupName) {
    if (confirm('Apakah Anda yakin ingin menghapus assignment untuk kelompok "' + groupName + '"?')) {
        // Buat form untuk DELETE request
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = '/assign-kelompok/hapus/' + groupId;
        
        // Tambahkan CSRF token
        var csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);
        
        // Tambahkan method override untuk DELETE
        var methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        form.appendChild(methodField);
        
        // Tambahkan form ke body dan submit
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
</x-app-layout> 
