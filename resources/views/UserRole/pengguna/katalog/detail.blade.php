@extends('layouts.pengguna')

@section('title', 'Detail Peralatan')

@section('style')
@endsection

@section('main')
    <!-- Notifikasi Alert -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Tombol Kembali -->
    <div class="mb-4">
        <a href="{{ route('katalog.list') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Katalog
        </a>
    </div>

    <!-- ============ BARIS UTAMA ============ -->
    <div class="row g-4 align-items-stretch mb-5">

        <!-- KOLOM KIRI: GAMBAR -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3 d-flex align-items-center justify-content-center">
                    @if($peralatan->gambar_peralatan)
                        <img src="{{ asset('uploads/gambar_peralatan/' . $peralatan->gambar_peralatan) }}" alt="{{ $peralatan->nama_barang }}" class="img-fluid rounded" style="max-height: 400px; object-fit: cover;">
                    @else
                        <div class="main-image">
                            <i class="bi bi-tools text-muted" style="font-size: 6rem;"></i>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- KOLOM KANAN: INFO + FORM -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column p-4">

                    <!-- Badge Kategori & Status -->
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="badge bg-primary px-3 py-2">{{ $peralatan->category->nama_kategori ?? 'Umum' }}</span>
                        @if($peralatan->stok_tersedia > 0)
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-20 px-3 py-2">
                                <i class="bi bi-check-circle me-1"></i>Tersedia
                            </span>
                        @else
                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-20 px-3 py-2">
                                <i class="bi bi-x-circle me-1"></i>Habis
                            </span>
                        @endif
                    </div>

                    <!-- Nama & Kode -->
                    <h2 class="fw-bold text-dark mt-2 mb-1">{{ $peralatan->nama_barang }}</h2>
                    <p class="text-muted font-monospace mb-3">
                        <i class="bi bi-upc-scan me-1"></i> Kode: <strong>{{ $peralatan->kode_barang }}</strong>
                    </p>

                    <!-- Harga -->
                    <div class="p-3 bg-light rounded mb-3">
                        <small class="text-muted d-block">Harga Sewa Per Hari</small>
                        <h3 class="text-primary fw-bold mb-0" id="hargaSatuan" data-harga="{{ $peralatan->price_per_day }}">
                            Rp {{ number_format($peralatan->price_per_day, 0, ',', '.') }}
                            <small class="text-muted fs-6">/ hari</small>
                        </h3>
                        <small class="text-danger d-block mt-2">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            Denda keterlambatan: Rp {{ number_format($peralatan->price_denda_perhari, 0, ',', '.') }} / hari
                        </small>
                    </div>

                    <!-- Stok -->
                    <div class="stok-info mb-3">
                        <div class="info-row d-flex justify-content-between mb-1">
                            <span class="text-muted small">Stok Total</span>
                            <strong>{{ $peralatan->stok_total }} unit</strong>
                        </div>
                        <div class="info-row d-flex justify-content-between">
                            <span class="text-muted small">Stok Tersedia</span>
                            <strong class="text-success">{{ $peralatan->stok_tersedia }} unit</strong>
                        </div>
                    </div>

                    <!-- Deskripsi -->
                    <div class="mb-3">
                        <h6 class="fw-bold">Deskripsi</h6>
                        <p class="text-muted small mb-0">
                            {{ $peralatan->deskripsi }}
                        </p>
                    </div>

                    <!-- FORM TAMBAH KE KERANJANG -->
                    <div class="mt-auto pt-3 border-top">
                        <form action="{{ route('keranjang.store', $peralatan->id) }}" method="POST" id="formTambahKeranjang">
                            @csrf
                            <div class="row g-2 align-items-end">
                                <div class="col-md-4">
                                    <label for="jumlah" class="form-label small mb-1">
                                        Jumlah <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="kurangJumlah()">
                                            <i class="bi bi-dash"></i>
                                        </button>
                                        <input type="number" id="jumlah" name="jumlah" class="form-control text-center"
                                            value="1" min="1" max="{{ $peralatan->stok_tersedia }}" required>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="tambahJumlah()">
                                            <i class="bi bi-plus"></i>
                                        </button>
                                    </div>
                                    <div class="form-text small">
                                        Maks: <strong>{{ $peralatan->stok_tersedia }}</strong> unit
                                    </div>
                                </div>

                                <div class="col-md-8">
                                    <button type="submit" class="btn btn-primary w-100 fw-bold btn-tambah py-2" @if($peralatan->stok_tersedia <= 0) disabled @endif>
                                        <i class="bi bi-cart-plus me-2"></i> Tambah ke Keranjang
                                    </button>
                                </div>
                            </div>
                        </form>

                        <!-- Ringkasan Harga -->
                        <div class="p-3 bg-light rounded mt-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small">Estimasi Total (per hari):</span>
                                <span id="totalHarga" class="fs-5 fw-bold text-primary">Rp {{ number_format($peralatan->price_per_day, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>
@endsection

@section('script')
<script>
    const maxStok = {{ $peralatan->stok_tersedia }};
    const hargaSatuan = parseInt(document.getElementById('hargaSatuan').getAttribute('data-harga'));
    const inputJumlah = document.getElementById('jumlah');
    const totalHargaEl = document.getElementById('totalHarga');

    function updateHargaTotal() {
        let jumlah = parseInt(inputJumlah.value) || 1;
        if (jumlah > maxStok) jumlah = maxStok;
        if (jumlah < 1) jumlah = 1;

        let total = jumlah * hargaSatuan;
        totalHargaEl.textContent = 'Rp ' + total.toLocaleString('id-ID');
    }

    function tambahJumlah() {
        let current = parseInt(inputJumlah.value) || 1;
        if (current < maxStok) {
            inputJumlah.value = current + 1;
            updateHargaTotal();
        }
    }

    function kurangJumlah() {
        let current = parseInt(inputJumlah.value) || 1;
        if (current > 1) {
            inputJumlah.value = current - 1;
            updateHargaTotal();
        }
    }

    inputJumlah.addEventListener('input', function() {
        updateHargaTotal();
    });
</script>
@endsection
