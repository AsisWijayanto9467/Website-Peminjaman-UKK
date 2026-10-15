@extends('layouts.pengguna');

@section('title', 'dashboard')

@section('style')

@endsection

@section('main')
    <!-- Header -->
    <div class="text-center mb-4">
        <h3><i class="bi bi-qr-code me-2"></i>Pembayaran QR Code</h3>
        <p class="text-muted">Selesaikan pembayaran Anda dengan memindai kode QR berikut</p>
    </div>

    <!-- Kartu QR Code -->
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card payment-card border-0 shadow-sm mb-4">
                <div class="card-body p-4 p-md-5 text-center">
                    <!-- Total Pembayaran -->
                    <div class="mb-4">
                        <span class="text-muted d-block mb-1">Total Pembayaran</span>
                        <h2 class="fw-bold text-primary mb-0">Rp 2.450.000</h2>
                    </div>

                    <!-- QR Code Besar -->
                    <div class="qr-container mb-4">
                        <img src="{{ asset("images/qr.jpg") }}"
                            alt="QR Code Pembayaran" class="qr-image">
                    </div>

                    <!-- Info Tambahan -->
                    <div class="mb-4">
                        <small class="text-muted d-block">
                            <i class="bi bi-shield-check me-1"></i> Pembayaran aman & terenkripsi
                        </small>
                        <small class="text-muted d-block mt-1">
                            Kode Order: <span class="fw-semibold">#RNT-2024-12345</span>
                        </small>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="d-flex flex-column flex-sm-row justify-content-center gap-3">
                        <a href="{{ route("keranjang.index") }}" class="btn btn-outline-secondary btn-action">
                            <i class="bi bi-arrow-left me-2"></i>Kembali
                        </a>
                        <a href="{{ route("penyewaan.booking") }}" class="btn btn-primary btn-action">
                            Selanjutnya<i class="bi bi-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Catatan -->
            <div class="text-center">
                <small class="text-muted">
                    <i class="bi bi-clock me-1"></i> QR Code berlaku selama 15 menit
                </small>
            </div>
        </div>
    </div>
@endsection

@section('script')

@endsection
