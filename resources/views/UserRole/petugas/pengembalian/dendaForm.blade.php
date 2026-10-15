@extends('layouts.admin')

@section('title', 'Buat Denda')

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
        padding: 16px;
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
    $penyewaan = $pengembalian->penyewaan;
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
                    <li class="breadcrumb-item">
                        <a href="{{ route('petugas.pengembalian.setting', $pengembalian->id) }}">
                            #{{ $penyewaan->kode_penyewaan ?? '-' }}
                        </a>
                    </li>
                    <li class="breadcrumb-item active">Buat Denda</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0">
                <i class="bi bi-exclamation-triangle text-danger me-2"></i>Buat Denda
            </h4>
            <p class="text-muted small mb-0">
                Masukkan nominal dan alasan denda untuk pengembalian yang ditolak.
            </p>
        </div>
        <a href="{{ route('petugas.pengembalian.setting', $pengembalian->id) }}"
           class="btn btn-outline-secondary rounded-3">
            <i class="bi bi-arrow-left me-2"></i>Kembali
        </a>
    </div>

    {{-- Flash --}}
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle me-1"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        {{-- ================= FORM ================= --}}
        <div class="col-lg-7">
            <div class="card detail-card">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-pencil-square me-2 text-primary"></i>Form Denda
                    </h5>
                </div>
                <div class="card-body">

                    <form action="{{ route('petugas.pengembalian.dendaStore', $pengembalian->id) }}"
                          method="POST"
                          onsubmit="return confirm('Simpan denda ini? Pastikan nominal dan alasan sudah benar.');">
                        @csrf

                        {{-- Info Booking --}}
                        <div class="alert alert-light border small rounded-3">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Kode Booking</span>
                                <span class="fw-bold font-monospace">
                                    {{ $penyewaan->kode_penyewaan ?? '-' }}
                                </span>
                            </div>
                            <div class="d-flex justify-content-between mt-1">
                                <span class="text-muted">Nama Peminjam</span>
                                <span class="fw-bold">{{ $penyewaan->customer_name ?? '-' }}</span>
                            </div>
                        </div>

                        {{-- Nominal --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">
                                Nominal Denda (Rp) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number"
                                       name="nominal"
                                       class="form-control @error('nominal') is-invalid @enderror"
                                       min="0"
                                       step="1000"
                                       placeholder="0"
                                       value="{{ old('nominal', $estimasiDenda > 0 ? $estimasiDenda : '') }}"
                                       required>
                                @error('nominal')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            @if ($estimasiDenda > 0)
                                <small class="text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Estimasi keterlambatan <strong>{{ $dendaHari }} hari</strong>:
                                    <strong>Rp {{ number_format($estimasiDenda, 0, ',', '.') }}</strong>
                                    (otomatis terisi, bisa diubah).
                                </small>
                            @endif
                        </div>

                        {{-- Alasan --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">
                                Alasan Denda <span class="text-danger">*</span>
                            </label>
                            <textarea name="alasan_denda"
                                      rows="4"
                                      class="form-control @error('alasan_denda') is-invalid @enderror"
                                      placeholder="Contoh: Barang dikembalikan dalam kondisi rusak / ada komponen hilang / terlambat."
                                      required>{{ old('alasan_denda') }}</textarea>
                            @error('alasan_denda')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Maksimal 1000 karakter.</small>
                        </div>

                        {{-- Catatan dari penolakan --}}
                        @if ($pengembalian->catatan_petugas)
                            <div class="alert alert-warning small rounded-3">
                                <strong><i class="bi bi-chat-left-text me-1"></i>Catatan dari Penolakan</strong>
                                <p class="mb-0 mt-1">{{ $pengembalian->catatan_petugas }}</p>
                            </div>
                        @endif

                        {{-- Action --}}
                        <div class="d-flex gap-2">
                            <a href="{{ route('petugas.pengembalian.setting', $pengembalian->id) }}"
                               class="btn btn-outline-secondary flex-grow-1 rounded-3">
                                <i class="bi bi-x-lg me-1"></i>Batal
                            </a>
                            <button type="submit" class="btn btn-danger flex-grow-1 rounded-3">
                                <i class="bi bi-save me-1"></i>Simpan Denda
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

        {{-- ================= INFO ================= --}}
        <div class="col-lg-5">
            <div class="card detail-card mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-info-circle me-2 text-primary"></i>Data Pengembalian
                    </h5>
                </div>
                <div class="card-body">
                    <div class="info-row">
                        <span class="info-label">Kode Booking</span>
                        <span class="info-value font-monospace">
                            {{ $penyewaan->kode_penyewaan ?? '-' }}
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Tanggal Sewa</span>
                        <span class="info-value">
                            {{ \Carbon\Carbon::parse($penyewaan->pickup_date)->format('d M Y') }}
                            -
                            {{ \Carbon\Carbon::parse($penyewaan->return_date)->format('d M Y') }}
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Tanggal Kembali Aktual</span>
                        <span class="info-value">
                            {{ \Carbon\Carbon::parse($pengembalian->actual_return_date)->format('d M Y') }}
                        </span>
                    </div>
                    @if ($dendaHari > 0)
                        <div class="info-row">
                            <span class="info-label">Keterlambatan</span>
                            <span class="info-value text-danger">{{ $dendaHari }} hari</span>
                        </div>
                    @endif
                    <div class="info-row">
                        <span class="info-label">Total Harga Sewa</span>
                        <span class="info-value text-success">
                            Rp {{ number_format($penyewaan->total_price ?? 0, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="card detail-card">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-box-seam me-2 text-primary"></i>Peralatan Disewa
                    </h5>
                </div>
                <div class="card-body p-0">
                    <table class="table align-middle mb-0 small">
                        <tbody>
                            @foreach ($penyewaan->detailPenyewaans as $d)
                                <tr>
                                    <td class="ps-3">
                                        {{ $d->peralatan->nama_barang ?? '-' }}
                                        <div class="text-muted font-monospace" style="font-size: 0.75rem;">
                                            {{ $d->peralatan->kode_barang ?? '-' }}
                                        </div>
                                    </td>
                                    <td class="text-center">{{ $d->jumlah }} unit</td>
                                    <td class="text-end pe-3 text-muted">
                                        Rp {{ number_format($d->peralatan->price_denda_perhari ?? 0, 0, ',', '.') }}/hari
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($pengembalian->photo_prove)
                <div class="card detail-card mt-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 fw-bold">
                            <i class="bi bi-camera me-2 text-primary"></i>Bukti Pengembalian
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="doc-preview">
                            <img src="{{ asset('uploads/pengembalian/prove/' . $pengembalian->photo_prove) }}"
                                 class="img-fluid rounded-3"
                                 style="max-height: 220px; object-fit: contain;">
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
