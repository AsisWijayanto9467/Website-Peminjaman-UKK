@extends('layouts.admin');

@section('title', 'dashboard')

@section('style')

@endsection

@section('main')
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">Manajemen Kategori</h4>
            <p class="text-muted mb-0">Kelola daftar kategori kendaraan yang tersedia.</p>
        </div>
        <a href="{{ route("kategori.create") }}" class="btn btn-primary rounded-3 px-4">
            <i class="bi bi-plus-lg me-1"></i> Tambah Kategori
        </a>
    </div>

    <!-- Card Tabel Kategori -->
    <div class="card border-0 shadow-sm rounded-3 category-table">
        <div class="card-header bg-white py-3 border-0">
            <form action="#" method="GET" class="w-100">
                <div class="input-group w-100">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text" name="search" class="form-control bg-light border-start-0 ps-0"
                        placeholder="Cari nama kategori...">
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
                            <th class="ps-4" style="width: 60px;">No</th>
                            <th>Nama Kategori</th>
                            <th>Deskripsi</th>
                            <th>Status</th>
                            <th class="text-end pe-4" style="width: 180px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($kategori as $k)
                            <tr>
                                <td class="ps-4 fw-bold">{{ $loop->iteration }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="fw-semibold text-dark">{{ $k->nama_kategori }}</span>
                                    </div>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $k->deskripsi_kategori }}</small>
                                </td>
                                <td>
                                    @if ($k->status === "aktif")
                                        <span class="fw-semibold text-warning">{{ $k->status }}</span>
                                    @else
                                        <span class="fw-semibold text-danger">{{ $k->status }}</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex gap-1 justify-content-end align-items-center ">
                                        <a href="{{ route("kategori.edit", $k->id) }}" class="btn btn-sm btn-outline-primary me-1" title="Edit Kategori">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </a>
                                        <form action="{{ route("kategori.destroy", $k->id) }}" class="mb-0" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus item ini?')">
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
                    Menampilkan 1 - 6 dari 6 data
                </small>
                <nav>
                    <ul class="pagination pagination-sm justify-content-end mb-0">
                        <li class="page-item disabled">
                            <a class="page-link" href="#" tabindex="-1">Previous</a>
                        </li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
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
