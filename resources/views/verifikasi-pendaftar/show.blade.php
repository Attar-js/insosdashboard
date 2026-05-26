<x-app-layout :assets="$assets ?? []">
@php
    $dosenName = $group->dosen->name ?? 'Belum ditentukan';
    $mitra = $group->nama_mitra ?? '-';
    $lokasi = $group->lokasi_mitra ?? $group->lokasi_kkn ?? '-';
@endphp

<div class="container-fluid px-3 px-lg-4 pb-4 verifikasi-pendaftar-detail">
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

    <div class="mb-4">
        <a href="{{ route('special-pages.pendaftar', request()->only(['filter', 'q'])) }}" class="vp-btn-back">
            <i class="fas fa-arrow-left"></i>
            <span>Kembali ke daftar</span>
        </a>
    </div>

    <div class="vp-project-card mb-4">
        <h4 class="vp-project-title">{{ $group->judul_kegiatan }}</h4>
        <div class="vp-project-meta">
            <div class="vp-project-meta-item">
                <span class="vp-project-meta-icon" aria-hidden="true"><i class="fas fa-handshake"></i></span>
                <div class="vp-project-meta-body">
                    <span class="vp-project-meta-label">Nama Mitra</span>
                    <span class="vp-project-meta-value">{{ $mitra }}</span>
                </div>
            </div>
            <div class="vp-project-meta-item">
                <span class="vp-project-meta-icon" aria-hidden="true"><i class="fas fa-map-marker-alt"></i></span>
                <div class="vp-project-meta-body">
                    <span class="vp-project-meta-label">Lokasi Mitra</span>
                    <span class="vp-project-meta-value">{{ $lokasi }}</span>
                </div>
            </div>
            <div class="vp-project-meta-item">
                <span class="vp-project-meta-icon" aria-hidden="true"><i class="fas fa-user-tie"></i></span>
                <div class="vp-project-meta-body">
                    <span class="vp-project-meta-label">Dosen Pembimbing</span>
                    <span class="vp-project-meta-value">{{ $dosenName }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <h5 class="vp-section-title">Anggota Kelompok {{ $group->nama_kelompok ?? 'KKN' }}</h5>
            <div class="card border-0 shadow-sm vp-card vp-members-card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0 vp-table-members">
                            <thead>
                                <tr>
                                    <th class="text-center">Nama</th>
                                    <th class="text-center">NIM</th>
                                    <th class="text-center">Peran</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($members as $member)
                                    <tr>
                                        <td class="vp-td-nama">{{ $member->mahasiswa->name ?? '-' }}</td>
                                        <td class="text-center">{{ $member->mahasiswa->nim ?? '-' }}</td>
                                        <td class="text-center">
                                            <span class="vp-role-badge {{ $member->isLeader() ? 'vp-role-badge--ketua' : '' }}">
                                                {{ $member->peranLabel() }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">Belum ada anggota kelompok.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm vp-card mb-3">
                <div class="card-body p-4">
                    <h6 class="vp-side-title">Status Verifikasi</h6>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted small">Persetujuan Dosen Pembimbing</span>
                        @if($dosenStatus === 'disetujui')
                            <span class="vp-status vp-status--success">Disetujui</span>
                        @elseif($dosenStatus === 'ditolak')
                            <span class="vp-status vp-status--danger">Ditolak</span>
                        @else
                            <span class="vp-status vp-status--warning">Menunggu</span>
                        @endif
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">Verifikasi Tim MK Penciri</span>
                        @if($isVerified)
                            <span class="vp-status vp-status--success">Disetujui</span>
                        @else
                            <span class="vp-status vp-status--warning">Menunggu</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm vp-card">
                <div class="card-body p-4">
                    <h6 class="vp-side-title">Proposal Kegiatan</h6>
                    @if($proposal && $proposal->file_name)
                        <div class="vp-proposal-file-box mb-3">
                            <i class="fas fa-file-pdf text-danger"></i>
                            <div>
                                <div class="fw-medium">{{ $proposalDisplayName ?? $proposal->file_name }}</div>
                                <small class="text-muted">
                                    PDF
                                    @if($proposalSizeKb)
                                        • {{ number_format($proposalSizeKb) }} KB
                                    @endif
                                </small>
                            </div>
                        </div>
                        <div class="vp-file-actions">
                            <button type="button" class="btn btn-sm vp-btn-lihat"
                                onclick="window.open('{{ route('files.pdf.proposal', $proposal->file_name) }}', '_blank')">
                                <i class="fas fa-eye me-1"></i> Lihat
                            </button>
                            <a href="{{ route('files.pdf.proposal.download', $proposal->file_name) }}"
                               class="btn btn-sm vp-btn-download">
                                <i class="fas fa-download me-1"></i> Download
                            </a>
                        </div>
                    @else
                        <p class="text-muted small mb-0">Proposal belum diunggah.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('special-pages.pendaftar.verifikasi', $group) }}" id="verifikasiKelompokForm">
        @csrf

        <div class="card border-0 shadow-sm vp-card vp-rubrik-card mb-3">
            <div class="card-body p-4">
                <div class="vp-rubrik-header mb-4">
                    <div class="vp-rubrik-header__text">
                        <h5 class="vp-rubrik-title mb-1">Rubrik Penilaian CPMK MK Inovasi Sosial</h5>
                        <p class="text-muted small mb-0">Deskripsi kegiatan diisi mahasiswa; Tim MK Penciri mengisi skor (0–100). Skor akhir terbobot dihitung otomatis.</p>
                    </div>
                    <div class="vp-skor-akhir-box text-center">
                        <small class="vp-skor-akhir-label">SKOR AKHIR</small>
                        <span class="vp-skor-akhir-value" id="vpSkorAkhir">{{ number_format($rubrikTotal, 1) }}</span>
                    </div>
                </div>

                @if(!$rubric->hasDeskripsi())
                    <div class="alert alert-info mb-3 py-2 small">
                        <i class="fas fa-info-circle me-1"></i>
                        Mahasiswa belum mengisi deskripsi kegiatan. Tim MK Penciri tetap dapat mengisi skor; verifikasi tetap memerlukan semua skor terisi.
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table align-middle mb-0 vp-rubrik-table">
                        <thead>
                            <tr>
                                <th>CPMK MK Inovasi Sosial</th>
                                <th>Deskripsi Kegiatan</th>
                                <th class="text-center">Bobot</th>
                                <th class="text-center">Skor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="vp-td-cpmk">Mahasiswa mampu menentukan program sebagai solusi pemecahan masalah di lokasi pengabdian masyarakat (P5)</td>
                                <td class="vp-td-deskripsi">{{ $rubric->deskripsi_p5 ?: '—' }}</td>
                                <td class="text-center vp-td-bobot">35%</td>
                                <td class="text-center">
                                    <input type="number" name="skor_p5" class="form-control vp-skor-input"
                                        min="0" max="100" step="0.01" placeholder="0-100"
                                        value="{{ old('skor_p5', $rubric->skor_p5) }}"
                                        {{ $canEditSkor ? '' : 'readonly' }}
                                        @if($canEditSkor) required @endif>
                                </td>
                            </tr>
                            <tr>
                                <td class="vp-td-cpmk">Mahasiswa mampu mengimplementasikan ilmu pengetahuan dan teknologi dalam kerja sama tim (C3)</td>
                                <td class="vp-td-deskripsi">{{ $rubric->deskripsi_c3 ?: '—' }}</td>
                                <td class="text-center vp-td-bobot">30%</td>
                                <td class="text-center">
                                    <input type="number" name="skor_c3" class="form-control vp-skor-input"
                                        min="0" max="100" step="0.01" placeholder="0-100"
                                        value="{{ old('skor_c3', $rubric->skor_c3) }}"
                                        {{ $canEditSkor ? '' : 'readonly' }}
                                        @if($canEditSkor) required @endif>
                                </td>
                            </tr>
                            <tr>
                                <td class="vp-td-cpmk">Mahasiswa mampu melaporkan hasil kegiatan pengabdian kepada masyarakat (A2)</td>
                                <td class="vp-td-deskripsi">{{ $rubric->deskripsi_a2 ?: '—' }}</td>
                                <td class="text-center vp-td-bobot">35%</td>
                                <td class="text-center">
                                    <input type="number" name="skor_a2" class="form-control vp-skor-input"
                                        min="0" max="100" step="0.01" placeholder="0-100"
                                        value="{{ old('skor_a2', $rubric->skor_a2) }}"
                                        {{ $canEditSkor ? '' : 'readonly' }}
                                        @if($canEditSkor) required @endif>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm vp-card vp-verify-bar">
            <div class="card-body p-0">
                <div class="vp-verify-bar__inner">
                    @if($isVerified)
                        <p class="mb-0 text-success small fw-medium vp-verify-bar__hint">
                            <i class="fas fa-check-circle me-1"></i> Kelompok ini sudah diverifikasi.
                        </p>
                    @elseif($canEditSkor)
                        <p class="mb-0 vp-verify-bar__hint">
                            Isi semua skor CPMK sebelum memverifikasi.
                        </p>
                    @elseif(!$group->isSupervisorApproved())
                        <p class="mb-0 vp-verify-bar__hint vp-verify-bar__hint--muted">
                            Menunggu persetujuan dosen pembimbing sebelum skor dapat diisi.
                        </p>
                    @endif

                    <div class="vp-verify-bar__actions">
                        @if($canEditSkor)
                            <button type="submit" class="btn vp-btn-verifikasi-kelompok">
                                Verifikasi Kelompok
                            </button>
                        @elseif(!$isVerified)
                            <button type="button" class="btn vp-btn-verifikasi-kelompok vp-btn-verifikasi-kelompok--disabled"
                                disabled
                                title="Skor dapat diisi setelah kelompok disetujui dosen pembimbing.">
                                Verifikasi Kelompok
                            </button>
                        @endif
                        <a href="{{ route('special-pages.pendaftar', request()->only(['filter', 'q'])) }}"
                           class="btn vp-btn-kembali-daftar">
                            Kembali ke Daftar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const inputs = document.querySelectorAll('.vp-skor-input:not([readonly])');
    const totalEl = document.getElementById('vpSkorAkhir');
    if (!inputs.length || !totalEl) return;

    const recalc = () => {
        const p5 = parseFloat(document.querySelector('[name="skor_p5"]')?.value) || 0;
        const c3 = parseFloat(document.querySelector('[name="skor_c3"]')?.value) || 0;
        const a2 = parseFloat(document.querySelector('[name="skor_a2"]')?.value) || 0;
        totalEl.textContent = ((p5 * 0.35) + (c3 * 0.30) + (a2 * 0.35)).toFixed(1);
    };

    inputs.forEach((el) => el.addEventListener('input', recalc));
    recalc();
});
</script>
@endpush
</x-app-layout>
