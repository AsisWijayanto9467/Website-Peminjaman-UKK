@extends('layouts.admin')

@section('title', 'Detail Denda - Petugas')

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
    .info-label { color: #6c757d; font-size: 0.85rem; }
    .info-value { font-weight: 600; text-align: right; }
</style>
@endsection

@section('main')
@php
    use Carbon\Carbon;
    $penyewaan = $denda->penyewaan;
    $pickup    = $penyewaan ? Carbon::parse($penyewaan->pickup_date) : null;
    $return    = $penyewaan ? Carbon::parse($penyewaan->return_date) : null;
    $durasi    = ($pickup && $return) ? ($pickup->diffInDays($return) ?: 1) : 0;
@endphp

<div class="container-fluid py-2">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item">
                        <a href="{{ route('petugas.denda.list') }}">Denda</a>
                    </li>
                    <li class="breadcrumb-item active">
                        {{ $denda->kode_denda }}
                    </li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0">Detail Denda</h4>
        </div>
        <a href="{{ route('petugas.denda.list') }}" class="btn btn-outline-secondary rounded-3">
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

            {{-- Info Denda --}}
            <div class="card detail-card mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-receipt me-2 text-danger"></i>Informasi Denda
                    </h5>
                    @if ($denda->status === 'sudah dibayar')
                        <span class="badge bg-success rounded-pill px-3 py-2">
                            <i class="bi bi-check-circle me-1"></i>Sudah Dibayar
                        </span>
                    @else
                        <span class="badge bg-warning text-dark rounded-pill px-3 py-2">
                            <i class="bi bi-hourglass-split me-1"></i>Belum Dibayar
                        </span>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="info-row">
                                <span class="info-label">Kode Denda</span>
                                <span class="info-value font-monospace text-primary">
                                    {{ $denda->kode_denda }}
                                </span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Kode Booking</span>
                                <span class="info-value font-monospace">
                                    {{ $penyewaan->kode_penyewaan ?? '-' }}
                                </span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Nama Peminjam</span>
                                <span class="info-value">{{ $penyewaan->customer_name ?? '-' }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-row">
                                <span class="info-label">Nominal</span>
                                <span class="info-value text-danger fs-6">
                                    Rp {{ number_format($denda->nominal, 0, ',', '.') }}
                                </span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Dibuat</span>
                                <span class="info-value">
                                    {{ optional($denda->created_at)->format('d M Y H:i') }}
                                </span>
                            </div>
                            @if ($denda->petugas)
                                <div class="info-row">
                                    <span class="info-label">Diproses Oleh</span>
                                    <span class="info-value">{{ $denda->petugas->name ?? '-' }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <hr>

                    <div>
                        <small class="text-muted d-block mb-1">Alasan Denda</small>
                        <div class="border rounded-3 p-3 bg-light">
                            {{ $denda->alasan_denda ?? '-' }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bukti Pembayaran Denda --}}
            <div class="card detail-card mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-image me-2 text-primary"></i>Bukti Pembayaran Denda
                    </h5>
                </div>
                <div class="card-body">
                    <div class="doc-preview">
                        @if ($denda->bukti_dibayar)
                            <img src="{{ asset('uploads/denda/bukti/' . $denda->bukti_dibayar) }}"
                                 alt="Bukti Pembayaran Denda"
                                 class="img-fluid rounded-3 mb-2"
                                 style="max-height: 320px; object-fit: contain;">
                            <div>
                                <a href="{{ asset('uploads/denda/bukti/' . $denda->bukti_dibayar) }}"
                                   target="_blank"
                                   class="btn btn-sm btn-primary rounded-3">
                                    <i class="bi bi-zoom-in me-1"></i>Lihat Penuh
                                </a>
                            </div>
                        @else
                            <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                            <p class="mt-2 mb-0 text-muted small fst-italic">
                                Belum ada bukti pembayaran dari pengguna.
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Detail Booking --}}
            @if ($penyewaan)
                <div class="card detail-card mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 fw-bold">
                            <i class="bi bi-info-circle me-2 text-primary"></i>Info Booking
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
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
                            </div>
                            <div class="col-md-6">
                                <div class="info-row">
                                    <span class="info-label">Total Harga Sewa</span>
                                    <span class="info-value text-success">
                                        Rp {{ number_format($penyewaan->total_price ?? 0, 0, ',', '.') }}
                                    </span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Status Booking</span>
                                    <span class="info-value">
                                        @php
                                            $statusMap = [
                                                'checking'  => ['label' => 'Verifikasi', 'class' => 'bg-secondary'],
                                                'denda'     => ['label' => 'Denda',      'class' => 'bg-danger'],
                                                'completed' => ['label' => 'Selesai',    'class' => 'bg-success'],
                                            ];
                                            $sp = $statusMap[$penyewaan->status] ?? ['label' => $penyewaan->status, 'class' => 'bg-secondary'];
                                        @endphp
                                        <span class="badge {{ $sp['class'] }}">{{ $sp['label'] }}</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

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
                                        <td class="ps-4">{{ $d->peralatan->nama_barang ?? '-' }}</td>
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
            @if ($denda->user)
                <div class="card detail-card mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 fw-bold">
                            <i class="bi bi-person me-2 text-primary"></i>Akun Pelanggan
                        </h5>
                    </div>
                    <div class="card-body text-center">
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center mb-3"
                             style="width: 70px; height: 70px; font-size: 1.5rem; font-weight: 700;">
                            {{ strtoupper(substr($denda->user->name, 0, 1)) }}
                        </div>
                        <h6 class="fw-bold mb-1">{{ $denda->user->name }}</h6>
                        <small class="text-muted d-block">{{ $denda->user->email }}</small>
                    </div>
                </div>
            @endif

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
                            <small class="text-muted">Status Denda</small>
                            @if ($denda->status === 'sudah dibayar')
                                <span class="badge bg-success rounded-pill">Sudah Dibayar</span>
                            @else
                                <span class="badge bg-warning text-dark rounded-pill">Belum Dibayar</span>
                            @endif
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small class="text-muted">Nominal</small>
                            <span class="fw-bold text-danger">
                                Rp {{ number_format($denda->nominal, 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">Bukti Bayar</small>
                            @if ($denda->bukti_dibayar)
                                <span class="badge bg-info">Tersedia</span>
                            @else
                                <span class="badge bg-secondary">Belum Ada</span>
                            @endif
                        </div>
                    </div>

                    {{-- ============ STATUS BELUM DIBAYAR: konfirmasi ============ --}}
                    @if ($denda->status === 'belum dibayar')
                        @if ($denda->bukti_dibayar)
                            <div class="alert alert-info small rounded-3 mb-3">
                                <strong><i class="bi bi-info-circle me-1"></i>Konfirmasi Pembayaran</strong>
                                <p class="mb-0 mt-1">
                                    Periksa bukti pembayaran di halaman utama.
                                    Jika nominal &amp; tanggal sesuai, klik konfirmasi.
                                </p>
                            </div>

                            <form action="{{ route('petugas.denda.confirm', $denda->id) }}"
                                  method="POST"
                                  onsubmit="return confirm('Konfirmasi bahwa denda sudah dibayar? Booking akan selesai & stok dikembalikan.');">
                                @csrf
                                <button type="submit" class="btn btn-success w-100 rounded-3">
                                    <i class="bi bi-check-circle me-2"></i>KONFIRMASI SUDAH DIBAYAR
                                </button>
                            </form>
                        @else
                            <div class="alert alert-secondary small rounded-3 mb-0">
                                <i class="bi bi-hourglass-split me-1"></i>
                                Menunggu pengguna mengunggah bukti pembayaran denda.
                            </div>
                        @endif
                    @endif

                    {{-- ============ SUDAH DIBAYAR ============ --}}
                    @if ($denda->status === 'sudah dibayar')
                        <div class="alert alert-success small rounded-3 mb-0">
                            <i class="bi bi-check2-all me-1"></i>
                            Denda telah <strong>dibayar</strong>.
                            Booking telah selesai &amp; stok peralatan telah dikembalikan.
                        </div>
                    @endif

                </div>
            </div>

        </div>
    </div>
</div>
@endsection
