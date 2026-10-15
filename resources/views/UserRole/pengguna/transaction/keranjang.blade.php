@extends('layouts.pengguna')

@section('title', 'Keranjang Sewa')

@section('style')
@endsection

@section('main')
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="bi bi-cart3 me-2"></i>Keranjang Sewa</h3>
        <span class="badge bg-primary rounded-pill fs-6 px-3 py-2">{{ $keranjangs->sum('jumlah') }} item</span>
    </div>

    @if($keranjangs->isEmpty())
        <div class="card border-0 shadow-sm text-center py-5">
            <div class="card-body">
                <i class="bi bi-cart-x text-muted" style="font-size: 4rem;"></i>
                <h5 class="mt-3 text-muted">Keranjang belanja Anda masih kosong</h5>
                <a href="{{ route('katalog.list') }}" class="btn btn-primary mt-3">Lihat Katalog Peralatan</a>
            </div>
        </div>
    @else
        <!-- Daftar Item Keranjang -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                @php $subtotal = 0; @endphp
                @foreach($keranjangs as $item)
                    @php
                        $totalItem = $item->peralatan->price_per_day * $item->jumlah;
                        $subtotal += $totalItem;
                    @endphp
                    <div class="row align-items-center cart-item-row mb-3 pb-3 border-bottom">
                        <div class="col-md-2 col-4 mb-2 mb-md-0">
                            <div style="width: 100%; height: 90px;" class="d-flex align-items-center justify-content-center bg-light rounded">
                                @if($item->peralatan->gambar_peralatan)
                                    <img src="{{ asset('uploads/gambar_peralatan/' . $item->peralatan->gambar_peralatan) }}" alt="" class="img-fluid rounded" style="max-height: 80px; object-fit: cover;">
                                @else
                                    <i class="bi bi-tools text-muted" style="font-size: 2rem;"></i>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4 col-8 mb-2 mb-md-0">
                            <h6 class="fw-bold mb-1">{{ $item->peralatan->nama_barang }}</h6>
                            <small class="text-muted d-block">{{ $item->peralatan->category->nama_kategori ?? '-' }} · Kode: {{ $item->peralatan->kode_barang }}</small>
                            <small class="text-primary fw-semibold">Rp {{ number_format($item->peralatan->price_per_day, 0, ',', '.') }} <span class="text-muted fw-normal">/hari</span></small>
                        </div>
                        <div class="col-md-3 col-6 d-flex align-items-center gap-2">
                            <form action="{{ route('keranjang.updateQty', [$item->id, 'kurang']) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button class="btn btn-outline-secondary qty-btn btn-sm" type="submit">−</button>
                            </form>
                            <span class="fw-bold fs-5 mx-1">{{ $item->jumlah }}</span>
                            <form action="{{ route('keranjang.updateQty', [$item->id, 'tambah']) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button class="btn btn-outline-secondary qty-btn btn-sm" type="submit" @if($item->peralatan->stok_tersedia < 1) disabled @endif>+</button>
                            </form>
                        </div>
                        <div class="col-md-2 col-6 text-md-end mt-2 mt-md-0">
                            <span class="fw-bold text-primary">Rp {{ number_format($totalItem, 0, ',', '.') }}</span>
                        </div>
                        <div class="col-md-1 col-12 text-end mt-2 mt-md-0">
                            <form action="{{ route('keranjang.destroy', $item->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger border-0" title="Hapus" onclick="return confirm('Yakin ingin menghapus item ini?')">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Ringkasan Total -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Total Item</span>
                    <span class="fw-semibold">{{ $keranjangs->sum('jumlah') }} unit</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Subtotal (per hari)</span>
                    <span class="fw-semibold">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                </div>
                <hr>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-bold fs-5">Total</span>
                    <span class="fw-bold fs-4 text-primary">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Tombol Aksi: Checkout & Kembali -->
        <div class="d-flex flex-column flex-sm-row justify-content-between gap-3 mb-5">
            <a href="{{ route('katalog.list') }}" class="btn btn-outline-secondary btn-lg order-sm-1">
                <i class="bi bi-arrow-left me-2"></i>Kembali
            </a>
            <a href="{{ route('penyewaan.qr') }}" class="btn btn-primary btn-lg order-sm-2">
                <i class="bi bi-credit-card me-2"></i>Checkout
            </a>
        </div>
    @endif
@endsection

@section('script')
@endsection
