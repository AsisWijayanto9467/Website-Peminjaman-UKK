@extends('layouts.admin')

@section('title', 'Detail Pengembalian - Petugas')

@section('style')
<style>
    .detail-card {
        border: none;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        border-radius: 12px;
    }
    .doc-preview {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
    }
    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px dashed #e9ecef;
        font-size: 0.9rem;
    }
    .info-row:last-child { border-bottom: none; }
    .info-label {
        color: #6c757d;
        font-size: 0.85rem;
    }
    .info-value {
        font-weight: 600;
        text-align: right;
    }
</style>
@endsection

@section('main')
@php
    use Carbon\Carbon;
    $penyewaan = $pengembalian->penyewaan;

    $pickup = $penyewaan ? Carbon::parse($penyewaan->pickup_date) : null;
    $return = $penyewaan ? Carbon::parse($penyewaan->return_date) : null;
    $actual = $pengembalian->actual_return_date ? Carbon::parse($pengembalian->actual_return_date) : null;
    $durasi = ($pickup && $return) ? ($pickup->diffInDays($return) ?: 1) : 0;

    $statusMap = [
        'padding' => ['label' => 'Menunggu Verifikasi', 'class' => 'bg-warning text-dark'],
        'accept'  => ['label' => 'Diterima',            'class' => 'bg-success'],
        'reject'  => ['label' => 'Ditolak',             'class' => 'bg-danger'],
    ];
    $st = $statusMap[$pengembalian->status] ?? ['label' => $pengembalian->status, 'class' => 'bg-secondary'];

    // Hitung denda keterlambatan
    $dendaHari = 0;
    $dendaTotal = 0;
    if ($actual && $return && $actual->greaterThan($return)) {
        $dendaHari = $return->diffInDays($actual);
        foreach ($penyewaan->detailPenyewaans as $d) {
            $dendaPerHari = $d->peralatan->price_denda_perhari ?? 0;
            $dendaTotal += $dendaPerHari * $d->jumlah * $dendaHari;
        }
    }
@endphp

<div class="container-fluid py-2">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item">
                        <a href="{{ route('petugas.pengembalian.list') }}">Pengembalian</a>
                    </li>
                    <li class="breadcrumb-item active">
                        #{{ $penyewaan->kode_penyewaan ?? '-' }}
                    </li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0">Detail Pengembalian</h4>
        </div>
        <a href="{{ route('petugas.pengembalian.list') }}" class="btn btn-outline-secondary rounded-3">
            <i class="bi bi-arrow-left me-2"></i>Kembali
        </a>
    </div>

    {{-- Flash --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-1"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle me-1"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        {{-- ================= MAIN ================= --}}
        <div class="col-lg-8">

            {{-- Info Booking --}}
            <div class="card detail-card mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-info-circle me-2 text-primary"></i>Informasi Booking
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="info-row">
                                <span class="info-label">Kode Booking</span>
                                <span class="info-value font-monospace text-primary">
                                    {{ $penyewaan->kode_penyewaan ?? '-' }}
                                </span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Nama Peminjam</span>
                                <span class="info-value">{{ $penyewaan->customer_name ?? '-' }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">No. KTP</span>
                                <span class="info-value">{{ $penyewaan->customer_id_card ?? '-' }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-row">
                                <span class="info-label">Tanggal Sewa</span>
                                <span class="info-value">
                                    {{ $pickup?->format('d M Y') }} - {{ $return?->format('d M Y') }}
                                </span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Durasi</span>
                                <span class="info-value">{{ $durasi }} Hari</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Total Harga</span>
                                <span class="info-value text-success">
                                    Rp {{ number_format($penyewaan->total_price ?? 0, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Info Pengembalian --}}
            <div class="card detail-card mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-arrow-return-left me-2 text-primary"></i>Data Pengembalian
                    </h5>
                    <span class="badge {{ $st['class'] }} rounded-pill px-3 py-2">{{ $st['label'] }}</span>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 bg-light">
                                <small class="text-muted d-block mb-1">Tanggal Pengajuan</small>
                                <strong>
                                    {{ optional($pengembalian->tanggal_pengajuan)->format('d M Y') ?? '-' }}
                                </strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 bg-light">
                                <small class="text-muted d-block mb-1">Tanggal Kembali Aktual</small>
                                <strong>
                                    {{ optional($pengembalian->actual_return_date)->format('d M Y') ?? '-' }}
                                </strong>
                                @if ($actual && $return)
                                    @if ($actual->greaterThan($return))
                                        <span class="badge bg-danger ms-1">
                                            Terlambat {{ $return->diffInDays($actual) }} hari
                                        </span>
                                    @else
                                        <span class="badge bg-success ms-1">Tepat Waktu</span>
                                    @endif
                                @endif
                            </div>
                        </div>

                        @if ($pengembalian->tanggal_konfirmasi)
                            <div class="col-md-6">
                                <div class="border rounded-3 p-3 bg-light">
                                    <small class="text-muted d-block mb-1">Tanggal Konfirmasi</small>
                                    <strong>
                                        {{ optional($pengembalian->tanggal_konfirmasi)->format('d M Y') }}
                                    </strong>
                                </div>
                            </div>
                        @endif

                        @if ($pengembalian->petugas)
                            <div class="col-md-6">
                                <div class="border rounded-3 p-3 bg-light">
                                    <small class="text-muted d-block mb-1">Diproses Oleh</small>
                                    <strong>{{ $pengembalian->petugas->name ?? '-' }}</strong>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Catatan Pengguna --}}
                    @if ($pengembalian->catatan_pengguna)
                        <div class="mb-3">
                            <small class="text-muted d-block mb-1">Catatan Pengguna</small>
                            <div class="border rounded-3 p-3">
                                {{ $pengembalian->catatan_pengguna }}
                            </div>
                        </div>
                    @endif

                    {{-- Catatan Petugas (kalau sudah diproses) --}}
                    @if ($pengembalian->catatan_petugas)
                        <div class="mb-3">
                            <small class="text-muted d-block mb-1">Catatan Petugas</small>
                            <div class="border rounded-3 p-3 bg-primary bg-opacity-10 text-primary">
                                {{ $pengembalian->catatan_petugas }}
                            </div>
                        </div>
                    @endif

                    {{-- Denda Info (kalau ada keterlambatan) --}}
                    @if ($dendaHari > 0)
                        <div class="alert alert-warning small rounded-3 mb-0">
                            <strong><i class="bi bi-exclamation-triangle me-1"></i>Keterlambatan Terdeteksi</strong>
                            <div class="mt-1">
                                Terlambat {{ $dendaHari }} hari dari jadwal.
                                Estimasi denda: <strong>Rp {{ number_format($dendaTotal, 0, ',', '.') }}</strong>
                            </div>
                            <small class="text-muted">
                                (Denda akan dihitung final di halaman denda)
                            </small>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Foto Bukti --}}
            <div class="card detail-card mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-camera me-2 text-primary"></i>Foto Bukti Pengembalian
                    </h5>
                </div>
                <div class="card-body">
                    <div class="doc-preview">
                        @if ($pengembalian->photo_prove)
                            <img src="{{ asset('uploads/pengembalian/prove/' . $pengembalian->photo_prove) }}"
                                 alt="Bukti Pengembalian"
                                 class="img-fluid rounded-3 mb-2"
                                 style="max-height: 320px; object-fit: contain;">
                            <div>
                                <a href="{{ asset('uploads/pengembalian/prove/' . $pengembalian->photo_prove) }}"
                                   target="_blank"
                                   class="btn btn-sm btn-primary rounded-3">
                                    <i class="bi bi-zoom-in me-1"></i>Lihat Penuh
                                </a>
                            </div>
                        @else
                            <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                            <p class="mt-2 mb-0 text-muted small fst-italic">
                                Belum ada foto bukti pengembalian.
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Detail Peralatan --}}
            @if ($penyewaan)
                <div class="card detail-card mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 fw-bold">
                            <i class="bi bi-box-seam me-2 text-primary"></i>Peralatan Disewa
                        </h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Peralatan</th>
                                    <th>Kode</th>
                                    <th>Jumlah</th>
                                    <th class="pe-4">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($penyewaan->detailPenyewaans as $d)
                                    <tr>
                                        <td class="ps-4">
                                            {{ $d->peralatan->nama_barang ?? '-' }}
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark font-monospace">
                                                {{ $d->peralatan->kode_barang ?? '-' }}
                                            </span>
                                        </td>
                                        <td>{{ $d->jumlah }} unit</td>
                                        <td class="pe-4 fw-semibold">
                                            Rp {{ number_format($d->subtotal, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        {{-- ================= SIDEBAR ================= --}}
        <div class="col-lg-4">

            {{-- Akun Pelanggan --}}
            <div class="card detail-card mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-person me-2 text-primary"></i>Akun Pelanggan
                    </h5>
                </div>
                <div class="card-body text-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center mb-3"
                         style="width: 70px; height: 70px; font-size: 1.5rem; font-weight: 700;">
                        {{ strtoupper(substr($penyewaan->customer_name ?? 'X', 0, 1)) }}
                    </div>
                    <h6 class="fw-bold mb-1">{{ $penyewaan->customer_name ?? '-' }}</h6>
                    <small class="text-muted d-block">{{ $penyewaan->user->email ?? '-' }}</small>
                </div>
            </div>

            {{-- Panel Aksi --}}
            <div class="card detail-card">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-gear me-2 text-primary"></i>Panel Aksi
                    </h5>
                </div>
                <div class="card-body">

                    {{-- Ringkasan --}}
                    <div class="border rounded-3 p-3 mb-3 bg-light">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small class="text-muted">Status Pengembalian</small>
                            <span class="badge {{ $st['class'] }} rounded-pill">{{ $st['label'] }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">Status Booking</small>
                            @php
                                $statusPenyewaan = $penyewaan->status ?? '-';
                                $statusPenyewaanMap = [
                                    'checking'  => ['label' => 'Verifikasi', 'class' => 'bg-secondary'],
                                    'denda'     => ['label' => 'Denda',      'class' => 'bg-danger'],
                                    'completed' => ['label' => 'Selesai',    'class' => 'bg-success'],
                                ];
                                $sp = $statusPenyewaanMap[$statusPenyewaan] ?? ['label' => $statusPenyewaan, 'class' => 'bg-secondary'];
                            @endphp
                            <span class="badge {{ $sp['class'] }} rounded-pill">{{ $sp['label'] }}</span>
                        </div>
                    </div>

                    {{-- ============ STATUS PADDING: Aksi accept/reject ============ --}}
                    @if ($pengembalian->status === 'padding')
                        <div class="alert alert-warning rounded-3 small mb-3">
                            <strong><i class="bi bi-hourglass-split me-1"></i> Menunggu Keputusan</strong>
                            <p class="mb-0 mt-1">
                                Periksa data pengembalian. Setujui jika kondisi baik, atau tolak jika ada masalah.
                            </p>
                        </div>

                        {{-- ACCEPT --}}
                        <form action="{{ route('petugas.pengembalian.accept', $pengembalian->id) }}"
                              method="POST" class="mb-3"
                              onsubmit="return confirm('Setujui pengembalian ini? Status booking akan berubah menjadi SELESAI.');">
                            @csrf
                            <button type="submit" class="btn btn-success w-100 rounded-3">
                                <i class="bi bi-check-circle me-2"></i>SETUJUI PENGEMBALIAN
                            </button>
                        </form>

                        <hr>

                        {{-- REJECT (dengan catatan) --}}
                        <form action="{{ route('petugas.pengembalian.reject', $pengembalian->id) }}"
                              method="POST"
                              onsubmit="return confirm('Tolak pengembalian ini? Status booking akan berubah menjadi ADA DENDA.');">
                            @csrf
                            <label class="form-label small fw-semibold">
                                Catatan Penolakan <span class="text-danger">*</span>
                            </label>
                            <textarea name="catatan_petugas"
                                      rows="3"
                                      class="form-control mb-2 @error('catatan_petugas') is-invalid @enderror"
                                      placeholder="Contoh: Barang rusak, ada komponen hilang, dsb.">{{ old('catatan_petugas') }}</textarea>
                            @error('catatan_petugas')
                                <div class="invalid-feedback d-block mb-2">{{ $message }}</div>
                            @enderror

                            <button type="submit" class="btn btn-outline-danger w-100 rounded-3">
                                <i class="bi bi-x-circle me-2"></i>TOLAK PENGEMBALIAN
                            </button>
                        </form>
                    @endif

                    {{-- ============ SUDAH DIPROSES ============ --}}
                    @if ($pengembalian->status === 'accept')
                        <div class="alert alert-success rounded-3 small mb-0">
                            <i class="bi bi-check2-all me-1"></i>
                            Pengembalian telah <strong>disetujui</strong>.
                            Booking selesai.
                        </div>
                    @endif

                   @if ($pengembalian->status === 'reject')
                        <div class="alert alert-danger rounded-3 small mb-3">
                            <i class="bi bi-x-octagon me-1"></i>
                            Pengembalian <strong>ditolak</strong>.
                        </div>

                        @if ($penyewaan && $penyewaan->status === 'denda')
                            @php
                                // Cek apakah denda sudah pernah dibuat
                                $dendaAda = \App\Models\Denda::where('pengembalian_id', $pengembalian->id)->first();
                            @endphp

                            @if ($dendaAda)
                                {{-- ✅ Denda sudah dibuat → tampilkan info + tombol lihat --}}
                                <div class="alert alert-info small rounded-3 mb-3">
                                    <strong><i class="bi bi-receipt me-1"></i>Denda Sudah Dibuat</strong>
                                    <div class="mt-2 small">
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">Kode</span>
                                            <span class="fw-bold font-monospace">{{ $dendaAda->kode_denda }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">Nominal</span>
                                            <span class="fw-bold text-danger">
                                                Rp {{ number_format($dendaAda->nominal, 0, ',', '.') }}
                                            </span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">Status</span>
                                            @if ($dendaAda->status === 'sudah dibayar')
                                                <span class="badge bg-success">Sudah Dibayar</span>
                                            @else
                                                <span class="badge bg-warning text-dark">Belum Dibayar</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <a href="{{ route('petugas.denda.setting', $dendaAda->id) }}"
                                class="btn btn-outline-danger w-100 rounded-3">
                                    <i class="bi bi-eye me-2"></i>LIHAT DETAIL DENDA
                                </a>
                            @else
                                {{-- Belum ada denda → tampilkan tombol buat --}}
                                <a href="{{ route('petugas.pengembalian.dendaForm', $pengembalian->id) }}"
                                class="btn btn-danger w-100 rounded-3">
                                    <i class="bi bi-exclamation-triangle me-2"></i>PROSES DENDA
                                </a>
                            @endif
                        @endif
                    @endif

                </div>
            </div>

        </div>
    </div>
</div>
@endsection
