@extends('layouts.admin');

@section('title', 'dashboard')

@section('style')

@endsection

@section('main')
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">Manajemen Kendaraan</h4>
            <p class="text-muted mb-0">Kelola daftar armada kendaraan, spesifikasi, dan ketersediaan.</p>
        </div>
        <a href="{{ route("peralatan.create") }}" class="btn btn-primary rounded-3 px-4">
            <i class="bi bi-plus-lg me-1"></i> Tambah Kendaraan
        </a>
    </div>

    <!-- Card Tabel Kendaraan -->
    <div class="card border-0 shadow-sm rounded-3 vehicle-table">
        <div class="card-header bg-white py-3 border-0">
            <form action="#" method="GET" class="w-100">
                <div class="input-group w-100">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text" name="search" class="form-control bg-light border-start-0 ps-0"
                        placeholder="Cari merk, plat nomor, atau kategori...">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-search me-1"></i> Cari
                    </button>
                </div>
            </form>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4" style="width: 70px;">No</th>
                            <th style="width: 100px;">Foto</th>
                            <th>Nama</th>
                            <th>Kode Alat</th>
                            <th>Stok</th>
                            <th>Harga / Hari</th>
                            <th>Denda / Hari</th>
                            <th>Status</th>
                            <th class="text-end pe-4" style="width: 200px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($peralatan as $p)
                            <tr>
                                <td class="ps-4 fw-bold">{{ $loop->iteration }}</td>
                                <td>
                                    <div class="vehicle-thumbnail bg-primary bg-opacity-10">
                                        @if ($p->gambar_peralatan && file_exists(public_path("uploads/gambar_peralatan/" . $p->gambar_peralatan)))
                                            <img src="{{ asset("uploads/gambar_peralatan/" . $p->gambar_peralatan) }}" alt="Avatar" style="width:100px;">
                                        @elseif (public_path("images/profile.jpg"))
                                            <img src="{{ asset("images/profile.jpg") }}" alt="Avatar" style="width:10px;">
                                        @else
                                            {{ strtoupper(substr($p->nama_barang, 0, 1)) }}
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-semibold text-dark">{{ $p->nama_barang }}</span>
                                </td>
                                <td>
                                    <span
                                        class="badge bg-secondary bg-opacity-10 text-dark border border-secondary border-opacity-20 font-monospace px-2 py-1">
                                        {{ $p->kode_barang }}
                                    </span>
                                </td>
                                <td>{{ $p->stok_total }} item</td>
                                <td class="fw-semibold text-primary">Rp {{ $p->price_per_day }}</td>
                                <td class="fw-semibold text-primary">Rp {{ $p->price_denda_perhari }}</td>
                                <td>
                                    @if ($p->status === "aktif")
                                        <span
                                            class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-20 px-2 py-1">
                                            <i class="bi bi-check-circle me-1"></i>Aktif
                                        </span>
                                    @else
                                        <span
                                            class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-20 px-2 py-1">
                                            <i class="bi bi-check-circle me-1"></i>Non Aktif
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex gap-1 justify-content-end align-items-center ">
                                        <a href="{{ route("peralatan.edit", $p->id) }}" class="btn btn-sm btn-outline-primary me-1" title="Edit Kategori">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </a>
                                        <form action="{{ route("peralatan.destroy", $p->id) }}" class="mb-0" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus item ini?')">
                                            @csrf
                                            @method("DELETE")
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Kategori">
                                                <i class="bi bi-trash"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">Belum ada Item disini</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top">
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    Menampilkan 1 - 6 dari 24 data
                </small>
                <nav>
                    <ul class="pagination pagination-sm justify-content-end mb-0">
                        <li class="page-item disabled">
                            <a class="page-link" href="#" tabindex="-1">Previous</a>
                        </li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item"><a class="page-link" href="#">4</a></li>
                        <li class="page-item">
                            <a class="page-link" href="#">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
@endsection

@section('script')

@endsection
