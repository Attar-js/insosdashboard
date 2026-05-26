<x-app-layout :assets="$assets ?? []">
<div class="container-fluid px-3 px-lg-4 pb-4 verifikasi-pendaftar-list">
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

    <div class="card border-0 shadow-sm vp-list-card">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <div class="vp-filter-tabs">
                    @php
                        $tabs = [
                            'semua' => 'Semua',
                            'menunggu_dosen' => 'Menunggu Dosen',
                            'perlu_diverifikasi' => 'Perlu Diverifikasi',
                            'terverifikasi' => 'Terverifikasi',
                        ];
                    @endphp
                    @foreach($tabs as $key => $label)
                        <a href="{{ route('special-pages.pendaftar', ['filter' => $key, 'q' => $search]) }}"
                           class="vp-filter-tab {{ $filter === $key ? 'vp-filter-tab--active' : '' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>

                <form method="GET" action="{{ route('special-pages.pendaftar') }}" class="vp-search-form">
                    <input type="hidden" name="filter" value="{{ $filter }}">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" name="q" class="form-control border-start-0 ps-0"
                               placeholder="Cari Kelompok, Dosen, Mitra..."
                               value="{{ $search }}">
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table align-middle mb-0 vp-list-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kelompok</th>
                            <th>Judul</th>
                            <th class="text-center">Dosen Pembimbing</th>
                            <th class="text-center">File Proposal</th>
                            <th class="text-center">Status Verifikasi Dosen</th>
                            <th class="text-center">Skor</th>
                            <th class="text-center">Status Verifikasi Penciri</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                            @php
                                $group = $row['group'];
                                $proposal = $row['proposal'];
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $group->nama_kelompok ?? 'Kelompok KKN' }}</div>
                                    <small class="text-muted">Ketua: {{ $row['ketua'] }}</small>
                                </td>
                                <td>{{ Str::limit($group->judul_kegiatan, 45) }}</td>
                                <td class="text-center">
                                    @if($group->dosen)
                                        <span class="vp-dosen-link">{{ $group->dosen->name }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($proposal && $proposal->file_name)
                                        <div class="vp-file-actions">
                                            <button type="button"
                                                class="btn btn-sm vp-btn-lihat"
                                                onclick="window.open('{{ route('files.pdf.proposal', $proposal->file_name) }}', '_blank')">
                                                Lihat
                                                <i class="fas fa-external-link-alt ms-1"></i>
                                            </button>
                                            <a href="{{ route('files.pdf.proposal.download', $proposal->file_name) }}"
                                               class="btn btn-sm vp-btn-download">
                                                Download
                                                <i class="fas fa-download ms-1"></i>
                                            </a>
                                        </div>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($row['dosen_status'] === 'disetujui')
                                        <span class="vp-status vp-status--success">Disetujui</span>
                                    @elseif($row['dosen_status'] === 'ditolak')
                                        <span class="vp-status vp-status--danger">Ditolak</span>
                                    @else
                                        <span class="vp-status vp-status--warning">Menunggu</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($row['skor'] !== null)
                                        <span class="fw-semibold text-dark">{{ number_format($row['skor'], 0) }}%</span>
                                    @else
                                        <span class="vp-status vp-status--warning">Menunggu</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($row['penciri_status'] === 'disetujui')
                                        <span class="vp-status vp-status--success">Disetujui</span>
                                    @else
                                        <span class="vp-status vp-status--warning">Menunggu</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($row['penciri_status'] === 'disetujui')
                                        <a href="{{ route('special-pages.pendaftar.show', ['group' => $group->id, 'filter' => $filter, 'q' => $search]) }}"
                                           class="btn btn-sm vp-btn-aksi vp-btn-aksi--done"
                                           title="Lihat detail kelompok yang sudah diverifikasi">
                                            <i class="fas fa-check-circle me-1"></i>
                                            Lihat Detail
                                        </a>
                                    @elseif($row['can_verify'])
                                        <a href="{{ route('special-pages.pendaftar.show', ['group' => $group->id, 'filter' => $filter, 'q' => $search]) }}"
                                           class="btn btn-sm vp-btn-aksi"
                                           title="Verifikasi kelompok">
                                            <i class="fas fa-edit"></i>
                                            Verifikasi
                                        </a>
                                    @else
                                        <button type="button" class="btn btn-sm vp-btn-aksi" disabled title="Menunggu persetujuan dosen">
                                            <i class="fas fa-edit"></i>
                                            Verifikasi
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-5">
                                    Tidak ada data kelompok untuk filter ini.
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
