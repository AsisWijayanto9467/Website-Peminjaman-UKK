@extends('layouts.pengguna')

@section('title', 'Bayar Denda')

@section('main')
@php
    use Carbon\Carbon;
    $penyewaan = $denda->penyewaan;
@endphp

<div class="container py-4" style="max-width: 720px;">

    {{-- Header --}}
    <div class="mb-4">
        <a href="{{ route('booking.list', Auth::id()) }}" class="text-decoration-none small">
            <i class="bi bi-arrow-left me-1"></i>Kembali ke Riwayat
        </a>
        <h5 class="fw-bold mt-2 mb-0">
            <i class="bi bi-exclamation-triangle text-danger me-2"></i>Bayar Denda
        </h5>
        <p class="text-muted small mb-0">
            Kode Denda:
            <span class="font-monospace fw-bold">{{ $denda->kode_denda }}</span>
        </p>
    </div>

    {{-- Flash --}}
    @if (session('success'))
        <div class="alert alert-success small">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger small">{{ session('error') }}</div>
    @endif

    {{-- Info Denda --}}
    <div class="card border-0 shadow-sm rounded-3 mb-3">
        <div class="card-body">
            <div class="alert alert-danger small rounded-3 mb-3">
                <strong><i class="bi bi-info-circle me-1"></i>Rincian Denda</strong>
                <p class="mb-0 mt-1">{{ $denda->alasan_denda }}</p>
            </div>

            <div class="row g-3 small">
                <div class="col-md-6">
                    <span class="text-muted d-block">Kode Booking</span>
                    <strong class="font-monospace">{{ $penyewaan->kode_penyewaan ?? '-' }}</strong>
                </div>
                <div class="col-md-6">
                    <span class="text-muted d-block">Nama Peminjam</span>
                    <strong>{{ $penyewaan->customer_name ?? '-' }}</strong>
                </div>
                <div class="col-12">
                    <hr class="my-1">
                </div>
                <div class="col-md-6">
                    <span class="text-muted d-block">Nominal Denda</span>
                    <h4 class="fw-bold text-danger mb-0">
                        Rp {{ number_format($denda->nominal, 0, ',', '.') }}
                    </h4>
                </div>
                <div class="col-md-6">
                    <span class="text-muted d-block">Status</span>
                    @if ($denda->status === 'belum dibayar')
                        <span class="badge bg-warning text-dark rounded-pill">Belum Dibayar</span>
                    @else
                        <span class="badge bg-success rounded-pill">Sudah Dibayar</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Bukti lama kalau ada --}}
    @if ($denda->bukti_dibayar)
        <div class="card border-0 shadow-sm rounded-3 mb-3">
            <div class="card-body">
                <small class="text-muted d-block mb-2">Bukti pembayaran sebelumnya:</small>
                <img src="{{ asset('uploads/denda/bukti/' . $denda->bukti_dibayar) }}"
                     class="img-fluid rounded border"
                     style="max-height: 200px;">
                <p class="small text-muted mt-2 mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    Upload ulang akan menggantikan file ini.
                </p>
            </div>
        </div>
    @endif

    {{-- Form Upload --}}
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-4">

            <form action="{{ route('denda.upload', $denda->id) }}"
                  method="POST"
                  enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-semibold small">
                        Bukti Pembayaran Denda <span class="text-danger">*</span>
                    </label>
                    <input type="file"
                           name="bukti_dibayar"
                           accept="image/jpg,image/jpeg,image/png"
                           class="form-control @error('bukti_dibayar') is-invalid @enderror"
                           required>
                    @error('bukti_dibayar')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">
                        Format JPG/JPEG/PNG, maks 2 MB. Pastikan nominal &amp; tanggal transfer terlihat jelas.
                    </small>
                </div>

                <div class="alert alert-info small rounded-3">
                    <i class="bi bi-info-circle me-1"></i>
                    Setelah diunggah, tunggu konfirmasi petugas. Setelah dikonfirmasi,
                    booking Anda akan <strong>selesai</strong>.
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('booking.list', Auth::id()) }}"
                       class="btn btn-outline-secondary flex-grow-1 rounded-3">
                        <i class="bi bi-x-lg me-1"></i>Batal
                    </a>
                    <button type="submit"
                            class="btn btn-danger flex-grow-1 rounded-3"
                            onclick="return confirm('Kirim bukti pembayaran denda ini?');">
                        <i class="bi bi-send me-1"></i>Kirim Bukti Bayar
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection
