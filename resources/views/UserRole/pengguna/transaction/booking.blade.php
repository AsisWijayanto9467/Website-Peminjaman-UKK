@extends('layouts.pengguna')

@section('title', 'Formulir Penyewaan')

@section('style')
@endsection

@section('main')
    <!-- Header Halaman -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-file-earmark-text text-primary me-2"></i>Formulir Pengajuan Penyewaan</h4>
            <p class="text-muted small mb-0">Lengkapi data diri dan detail penyewaan kendaraan Anda di bawah ini.</p>
        </div>
        <a href="{{ route('keranjang.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <!-- Form Card -->
    <div class="card p-4">
        <form action="{{ route('penyewaan.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row g-3">
                <!-- Kode Penyewaan -->
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Kode Penyewaan</label>
                    <input type="text" name="kode_penyewaan" class="form-control font-monospace" value="BK-{{ date('Ymd') }}-{{ rand(100, 999) }}" readonly>
                </div>

                <!-- Customer Name -->
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Nama Lengkap</label>
                    <input type="text" name="customer_name" class="form-control" value="{{ Auth::user()->name ?? '' }}" placeholder="Masukkan nama lengkap" required>
                </div>

                <!-- Customer ID Card -->
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Nomor Identitas / KTP</label>
                    <input type="text" name="customer_id_card" class="form-control font-monospace" placeholder="Masukkan Nomor KTP" required>
                </div>

                <!-- Customer Address -->
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Alamat Lengkap</label>
                    <textarea name="customer_address" class="form-control" rows="1" placeholder="Masukkan alamat lengkap" required></textarea>
                </div>

                <!-- Pickup & Return Date -->
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Tanggal Pengambilan</label>
                    <input type="date" name="pickup_date" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Tanggal Pengembalian</label>
                    <input type="date" name="return_date" class="form-control" required>
                </div>

                <hr class="my-3">

                <!-- Upload Photos -->
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Foto Wajah</label>
                    <input type="file" name="face_photo" class="form-control form-control-sm">
                    <div class="form-text small">Format: JPG/PNG, Maks. 2MB</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Foto KTP</label>
                    <input type="file" name="id_card_photo" class="form-control form-control-sm">
                    <div class="form-text small">Format: JPG/PNG, Maks. 2MB</div>
                </div>

                <!-- Submit Button -->
                <div class="col-12 text-end mt-4">
                    <button type="reset" class="btn btn-secondary me-2">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                    </button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save me-1"></i> Ajukan Penyewaan
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('script')
@endsection
