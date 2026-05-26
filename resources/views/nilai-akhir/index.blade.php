<x-app-layout :assets="$assets ?? []">
<div class="container-fluid px-3 px-lg-4 pb-4 penilaian-akhir-list">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm na-list-card">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                <div>
                    <h5 class="na-list-title mb-1">Daftar Mahasiswa</h5>
                    <p class="text-muted small mb-0">{{ $total }} mahasiswa</p>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-2 na-list-toolbar">
                    <form method="GET" action="{{ route('nilai-akhir.index') }}" class="na-search-form">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input type="text" name="q" class="form-control border-start-0 ps-0"
                                   placeholder="Cari nama, NIM, kelompok, dosen..."
                                   value="{{ $search }}">
                        </div>
                    </form>
                    <a href="{{ route('nilai-akhir.export', request()->only('q')) }}"
                       class="btn btn-sm na-btn-export na-btn-export--excel">
                        <i class="fas fa-file-excel me-1"></i> Ekspor Excel
                    </a>
                    <a href="{{ route('nilai-akhir.export.csv', request()->only('q')) }}"
                       class="btn btn-sm na-btn-export na-btn-export--csv">
                        <i class="fas fa-file-csv me-1"></i> Ekspor CSV
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle mb-0 na-list-table">
                    <thead>
                        <tr>
                            <th class="text-center">No</th>
                            <th class="text-center">NIM</th>
                            <th>Nama Mahasiswa</th>
                            <th class="text-center">Peran</th>
                            <th>Kelompok</th>
                            <th class="text-center">Nilai Akhir</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                            <tr>
                                <td class="text-center">{{ $row['no'] }}</td>
                                <td class="text-center"><code class="na-nim">{{ $row['nim'] }}</code></td>
                                <td class="na-td-nama">{{ $row['nama'] }}</td>
                                <td class="text-center">
                                    <span class="na-role-badge {{ $row['is_ketua'] ? 'na-role-badge--ketua' : '' }}">
                                        {{ $row['peran'] }}
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $row['nama_kelompok'] }}</div>
                                    <small class="text-muted">{{ Str::limit($row['judul_kegiatan'], 60) }}</small>
                                </td>
                                <td class="text-center">
                                    @if($row['sudah_dinilai'])
                                        <span class="na-nilai-badge">{{ number_format((float) $row['nilai_akhir'], 1) }}</span>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('nilai-akhir.detail', $row['nim']) }}{{ $search ? '?q=' . urlencode($search) : '' }}"
                                       class="btn btn-sm na-btn-lihat-nilai">
                                        Lihat Nilai
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    @if($search)
                                        Tidak ada mahasiswa yang cocok dengan pencarian &ldquo;{{ $search }}&rdquo;.
                                    @else
                                        Belum ada data mahasiswa aktif di kelompok KKN.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</x-app-layout>
