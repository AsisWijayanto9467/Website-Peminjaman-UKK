@extends("layouts.pengguna")

@section("title", "Katalog Peralatan")

@section("style")
<style>
    .vehicle-thumb-placeholder {
        height: 180px;
        background-color: #e9ecef;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 4rem;
        color: #adb5bd;
        overflow: hidden;
    }
    .vehicle-thumb-placeholder img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .vehicle-card {
        transition: transform 0.2s, box-shadow 0.2s;
        border: none;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    .vehicle-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 6px 16px rgba(0,0,0,0.12);
    }
</style>
@endsection

@section("main")
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="bi bi-tools me-2"></i>Katalog Peralatan</h3>
    </div>

    <!-- Search dan Filter -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body">
            <form action="{{ route('katalog.list') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control bg-light"
                                   placeholder="Cari nama atau kode barang..."
                                   value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select name="kategori" class="form-select bg-light">
                            <option value="">Semua Kategori</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}"
                                    {{ request('kategori') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->nama_kategori }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-funnel me-1"></i> Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Info Jumlah Hasil -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <small class="text-muted">
            Menampilkan <strong>{{ $peralatans->count() }}</strong>
            dari <strong>{{ $peralatans->total() }}</strong> peralatan
        </small>
        @if (request('search') || request('kategori'))
            <a href="{{ route('katalog.list') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-x-circle me-1"></i> Reset Filter
            </a>
        @endif
    </div>

    <!-- Grid Peralatan -->
    <div class="row">
        @forelse ($peralatans as $p)
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card vehicle-card h-100">
                    <!-- Thumbnail -->
                    <div class="vehicle-thumb-placeholder">
                        @if ($p->gambar_peralatan && file_exists(public_path('uploads/gambar_peralatan/' . $p->gambar_peralatan)))
                            <img src="{{ asset('uploads/gambar_peralatan/' . $p->gambar_peralatan) }}"
                                 alt="{{ $p->nama_barang }}">
                        @else
                            <i class="bi bi-tools"></i>
                        @endif
                    </div>

                    <div class="card-body d-flex flex-column">
                        <!-- Nama & Status -->
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="card-title mb-0 fw-bold">{{ $p->nama_barang }}</h6>

                            @if ($p->status === 'aktif' && $p->stok_tersedia > 0)
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-20 px-2 py-1">
                                    <i class="bi bi-check-circle me-1"></i><small>Tersedia</small>
                                </span>
                            @elseif ($p->status === 'aktif' && $p->stok_tersedia == 0)
                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-20 px-2 py-1">
                                    <i class="bi bi-exclamation-circle me-1"></i><small>Habis</small>
                                </span>
                            @else
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-20 px-2 py-1">
                                    <i class="bi bi-x-circle me-1"></i><small>Nonaktif</small>
                                </span>
                            @endif
                        </div>

                        <!-- Info -->
                        <div class="mb-2">
                            <small class="text-muted d-block mb-1">
                                <i class="bi bi-upc-scan me-1"></i> {{ $p->kode_barang }}
                            </small>
                            <small class="text-muted d-block mb-1">
                                <i class="bi bi-tag me-1"></i> {{ $p->category->nama_kategori ?? 'Tanpa Kategori' }}
                            </small>
                            <small class="text-muted d-block">
                                <i class="bi bi-box-seam me-1"></i> Stok: {{ $p->stok_tersedia }} / {{ $p->stok_total }}
                            </small>
                        </div>

                        <!-- Harga -->
                        <h5 class="text-primary mb-3 mt-auto">
                            Rp {{ number_format($p->price_per_day, 0, ',', '.') }}
                            <small class="text-muted fs-6">/hari</small>
                        </h5>

                        <!-- Tombol -->
                        <div class="d-grid gap-2">
                            @if ($p->status === 'aktif' && $p->stok_tersedia > 0)
                                <a href="{{ route('katalog.detail', $p->id) }}" class="btn btn-primary btn-sm">
                                    <i class="bi bi-cart-plus me-1"></i> Sewa
                                </a>
                            @else
                                <button class="btn btn-secondary btn-sm" disabled>
                                    <i class="bi bi-lock me-1"></i> Tidak Tersedia
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-warning text-center mb-0">
                    <i class="bi bi-inbox me-2"></i>
                    Tidak ada peralatan yang ditemukan.
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if ($peralatans->hasPages())
        <div class="d-flex justify-content-center mt-3">
            {{ $peralatans->withQueryString()->links() }}
        </div>
    @endif
@endsection

@section("script")
@endsection
