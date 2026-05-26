@extends('layouts.dashboard.dashboard')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Status Pendaftaran KKN</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">Daftar Pendaftaran KKN</h4>
                    
                    @if($pendaftar->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-centered table-nowrap mb-0">
                                <thead>
                                    <tr>
                                        <th scope="col">No</th>
                                        <th scope="col">Judul Kegiatan</th>
                                        <th scope="col">Mitra</th>
                                        <th scope="col">Lokasi Mitra</th>
                                        <th scope="col">NIM Ketua</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Tanggal Daftar</th>
                                        <th scope="col">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendaftar as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <strong>{{ $item->judul_kegiatan }}</strong>
                                        </td>
                                        <td>{{ $item->mitra }}</td>
                                        <td>{{ $item->lokasi_mitra }}</td>
                                        <td>{{ $item->user_nim }}</td>
                                        <td>
                                            @if($item->status == 'approved')
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check me-1"></i>Diterima
                                                </span>
                                            @elseif($item->status == 'rejected')
                                                <span class="badge bg-danger">
                                                    <i class="fas fa-times me-1"></i>Ditolak
                                                </span>
                                            @else
                                                <span class="badge bg-warning">
                                                    <i class="fas fa-clock me-1"></i>Menunggu
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $item->created_at->format('d/m/Y H:i') }}
                                            </small>
                                        </td>
                                        <td>
                                            <a href="{{ route('kkn.detail', $item->id) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye me-1"></i>Detail
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Belum ada pendaftaran KKN</h5>
                            <p class="text-muted">Data pendaftaran akan muncul di sini setelah ada yang mendaftar.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 