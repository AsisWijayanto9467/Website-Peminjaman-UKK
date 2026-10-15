@extends('layouts.pengguna')

@section('title', 'Upload Bukti Pembayaran')

@section('main')
<div class="container py-4" style="max-width: 560px;">

    {{-- Header --}}
    <div class="mb-4">
        <a href="{{ route('booking.list', Auth::id()) }}" class="text-decoration-none small">
            <i class="bi bi-arrow-left me-1"></i>Kembali ke Riwayat
        </a>
        <h5 class="fw-bold mt-2 mb-0">
            <i class="bi bi-credit-card text-primary me-2"></i>Upload Bukti Pembayaran
        </h5>
        <p class="text-muted small mb-0">
            Booking <span class="font-monospace fw-bold">{{ $penyewaan->kode_penyewaan }}</span>
            &middot; Rp {{ number_format($penyewaan->total_price, 0, ',', '.') }}
        </p>
    </div>

    {{-- Flash --}}
    @if (session('success'))
        <div class="alert alert-success small">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger small">{{ session('error') }}</div>
    @endif

    {{-- Form --}}
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-4">

            <form action="{{ route('booking.payment.upload', $penyewaan->id) }}"
                  method="POST"
                  enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-semibold small">
                        Bukti Pembayaran <span class="text-danger">*</span>
                    </label>
                    <input type="file"
                           name="payment_photo"
                           accept="image/jpg,image/jpeg,image/png"
                           class="form-control @error('payment_photo') is-invalid @enderror"
                           required>
                    @error('payment_photo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Format JPG/JPEG/PNG, maksimal 2 MB.</small>
                </div>

                @if ($penyewaan->payment_photo)
                    <div class="mb-3">
                        <small class="text-muted d-block mb-1">Bukti sebelumnya:</small>
                        <img src="{{ asset('uploads/penyewaan/payment/' . $penyewaan->payment_photo) }}"
                             class="img-fluid rounded border"
                             style="max-height: 180px;">
                    </div>
                @endif

                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-send me-1"></i>Kirim
                </button>
            </form>

        </div>
    </div>
</div>
@endsection
