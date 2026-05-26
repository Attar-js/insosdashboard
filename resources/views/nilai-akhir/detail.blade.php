<x-app-layout :assets="$assets ?? []">
@php
    $p = $student['penilaian'] ?? null;
    $search = request('q', '');
@endphp

<div class="container-fluid px-3 px-lg-4 pb-4 penilaian-akhir-detail">
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="mb-4">
        <a href="{{ route('nilai-akhir.index', $search ? ['q' => $search] : []) }}" class="na-btn-back">
            <i class="fas fa-arrow-left"></i>
            <span>Kembali ke daftar</span>
        </a>
    </div>

    <div class="card border-0 shadow-sm na-card mb-4">
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-lg-8">
                    <h4 class="na-student-name mb-1">{{ $student['nama'] }}</h4>
                    <p class="text-muted mb-3">
                        <span class="me-3"><strong>NIM:</strong> {{ $student['nim'] }}</span>
                        <span class="na-role-badge {{ $student['peran'] === 'Ketua' ? 'na-role-badge--ketua' : '' }}">{{ $student['peran'] }}</span>
                    </p>
                    <div class="na-info-grid">
                        <div class="na-info-item">
                            <span class="na-info-label">Kelompok</span>
                            <span class="na-info-value">{{ $student['nama_kelompok'] }}</span>
                        </div>
                        <div class="na-info-item">
                            <span class="na-info-label">Judul Kegiatan</span>
                            <span class="na-info-value">{{ $student['judul_kegiatan'] }}</span>
                        </div>
                        <div class="na-info-item">
                            <span class="na-info-label">Dosen Pembimbing</span>
                            <span class="na-info-value">{{ $student['dosen_nama'] }}</span>
                        </div>
                        <div class="na-info-item">
                            <span class="na-info-label">Mitra</span>
                            <span class="na-info-value">{{ $student['nama_mitra'] }} — {{ $student['lokasi_mitra'] }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="na-skor-akhir-box text-center">
                        <small class="na-skor-akhir-label">NILAI AKHIR</small>
                        @if($student['sudah_dinilai'] && $p)
                            <span class="na-skor-akhir-value">{{ number_format((float) $p['nilai_akhir'], 1) }}</span>
                            @if($p['tanggal_penilaian'])
                                <small class="d-block text-muted mt-1">
                                    {{ \Carbon\Carbon::parse($p['tanggal_penilaian'])->format('d M Y') }}
                                </small>
                            @endif
                        @else
                            <span class="na-skor-akhir-value na-skor-akhir-value--empty">—</span>
                            <small class="d-block text-muted mt-1">Belum dinilai dosen</small>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm na-card">
        <div class="card-body p-4">
            <h5 class="na-section-title mb-3">Komponen Penilaian Dosen Pembimbing</h5>

            @if(!$p)
                <div class="alert alert-info mb-0">
                    <i class="fas fa-info-circle me-1"></i>
                    Dosen pembimbing belum mengisi penilaian akhir untuk mahasiswa ini.
                </div>
            @else
                <div class="row g-3">
                    @php
                        $komponen = [
                            ['label' => 'Proposal Kegiatan', 'bobot' => '20%', 'key' => 'proposal_kegiatan', 'icon' => 'fa-file-alt'],
                            ['label' => 'Asistensi', 'bobot' => '10%', 'key' => 'asistensi', 'icon' => 'fa-hands-helping'],
                            ['label' => 'Peer Review', 'bobot' => '15%', 'key' => 'peer_review', 'icon' => 'fa-users'],
                            ['label' => 'Laporan Akhir', 'bobot' => '20%', 'key' => 'laporan_akhir', 'icon' => 'fa-book'],
                            ['label' => 'Presentasi Akhir', 'bobot' => '15%', 'key' => 'presentasi_akhir', 'icon' => 'fa-chalkboard-teacher'],
                            ['label' => 'Pembimbing Lapangan', 'bobot' => '20%', 'key' => 'pembimbing_lapangan', 'icon' => 'fa-map-marked-alt'],
                        ];
                    @endphp
                    @foreach($komponen as $item)
                        <div class="col-md-6 col-lg-4">
                            <div class="na-komponen-card">
                                <div class="na-komponen-card__icon">
                                    <i class="fas {{ $item['icon'] }}"></i>
                                </div>
                                <div>
                                    <div class="na-komponen-card__label">{{ $item['label'] }}</div>
                                    <small class="text-muted">Bobot {{ $item['bobot'] }}</small>
                                    <div class="na-komponen-card__nilai">
                                        @if(isset($p[$item['key']]) && $p[$item['key']] !== null && $p[$item['key']] !== '')
                                            {{ number_format((float) $p[$item['key']], 1) }}
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
</x-app-layout>
