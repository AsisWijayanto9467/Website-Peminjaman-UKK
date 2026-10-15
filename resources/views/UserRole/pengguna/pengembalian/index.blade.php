@extends('layouts.pengguna')

@section('title', 'Ajukan Pengembalian')

@section('main')
<div class="container py-4" style="max-width: 720px;">

    {{-- Header --}}
    <div class="mb-4">
        <a href="{{ route('booking.list', Auth::id()) }}" class="text-decoration-none small">
            <i class="bi bi-arrow-left me-1"></i>Kembali ke Riwayat
        </a>
        <h5 class="fw-bold mt-2 mb-0">
            <i class="bi bi-arrow-return-left text-success me-2"></i>Ajukan Pengembalian
        </h5>
        <p class="text-muted small mb-0">
            Booking <span class="font-monospace fw-bold">{{ $penyewaan->kode_penyewaan }}</span>
        </p>
    </div>

    {{-- Flash --}}
    @if (session('error'))
        <div class="alert alert-danger small">{{ session('error') }}</div>
    @endif

    {{-- Info Penyewaan --}}
    <div class="card border-0 shadow-sm rounded-3 mb-3">
        <div class="card-body">
            <div class="row g-3 small">
                <div class="col-md-6">
                    <span class="text-muted d-block">Nama Peminjam</span>
                    <strong>{{ $penyewaan->customer_name }}</strong>
                </div>
                <div class="col-md-6">
                    <span class="text-muted d-block">Jadwal Pengembalian</span>
                    <strong>{{ $returnDate->format('d M Y') }}</strong>
                </div>
                <div class="col-12">
                    <span class="text-muted d-block mb-1">Peralatan</span>
                    @foreach ($penyewaan->detailPenyewaans as $d)
                        <div class="d-flex justify-content-between border-bottom py-1">
                            <span>{{ $d->peralatan->nama_barang ?? '-' }} ({{ $d->jumlah }} unit)</span>
                            <span class="text-muted font-monospace">
                                {{ $d->peralatan->kode_barang ?? '-' }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Form --}}
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-4">

            <form action="{{ route('pengembalian.store', $penyewaan->id) }}"
                  method="POST"
                  enctype="multipart/form-data">
                @csrf

                {{-- Info Tanggal Pengajuan (read-only) --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Tanggal Pengajuan</label>
                    <input type="text"
                           class="form-control bg-light"
                           value="{{ now()->translatedFormat('d F Y') }}"
                           readonly>
                    <small class="text-muted">
                        Jadwal pengembalian: {{ $returnDate->format('d M Y') }}
                        &middot; Tanggal pengajuan otomatis dari sistem.
                    </small>
                </div>

                {{-- Foto Bukti --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold small">
                        Foto Bukti Pengembalian
                    </label>
                    <input type="file"
                           name="photo_prove"
                           accept="image/jpg,image/jpeg,image/png"
                           class="form-control @error('photo_prove') is-invalid @enderror">
                    @error('photo_prove')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">
                        Opsional. Format JPG/JPEG/PNG, maks 2 MB.
                    </small>
                </div>

                {{-- Catatan Pengguna --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Catatan Pengguna</label>
                    <textarea name="catatan_pengguna"
                              rows="3"
                              class="form-control @error('catatan_pengguna') is-invalid @enderror"
                              placeholder="Contoh: Barang dikembalikan dalam kondisi baik, tanpa kerusakan.">{{ old('catatan_pengguna') }}</textarea>
                    @error('catatan_pengguna')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Info --}}
                <div class="alert alert-info small rounded-3">
                    <i class="bi bi-info-circle me-1"></i>
                    Setelah diajukan, status booking akan berubah menjadi
                    <strong>Verifikasi Pengembalian</strong> dan menunggu konfirmasi petugas.
                </div>

                {{-- Action --}}
                <div class="d-flex gap-2">
                    <a href="{{ route('booking.list', Auth::id()) }}"
                       class="btn btn-outline-secondary flex-grow-1">
                        <i class="bi bi-x-lg me-1"></i>Batal
                    </a>
                    <button type="submit"
                            class="btn btn-success flex-grow-1"
                            onclick="return confirm('Ajukan pengembalian untuk booking ini?');">
                        <i class="bi bi-send me-1"></i>Ajukan Pengembalian
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection
