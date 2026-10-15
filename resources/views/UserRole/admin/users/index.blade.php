@extends('layouts.admin');

@section('title', 'dashboard')

@section('style')

@endsection

@section('main')
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">Manajemen User</h4>
            <p class="text-muted mb-0">Kelola daftar pengguna, peran, dan informasi kontak.</p>
        </div>
        <a href="{{ route("user.create") }}" class="btn btn-primary rounded-3 px-4">
            <i class="bi bi-plus-lg me-1"></i> Tambah User
        </a>
    </div>

    <!-- Card Tabel User -->
    <div class="card border-0 shadow-sm rounded-3 user-table">
        <div class="card-header bg-white py-3 border-0">
            <form action="#" method="GET" class="w-100">
                <div class="input-group w-100">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text" name="search" class="form-control bg-light border-start-0 ps-0"
                        placeholder="Cari nama, email, atau telepon...">
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
                            <th class="ps-4" style="width: 80px;">No</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Telepon</th>
                            <th>Role</th>
                            <th class="text-end pe-4" style="width: 200px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($user as $u)
                            <tr>
                                <td class="ps-4 fw-bold">{{ $loop->iteration }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="user-avatar me-2 shadow-sm">
                                            @if ($u->photo_profile && file_exists(public_path("uploads/photo_profile/" . $u->photo_profile)))
                                                <img src="{{ asset("uploads/photo_profile/" . $u->photo_profile) }}" alt="Avatar">
                                            @elseif (file_exists(public_path("images/profile.jpg")))
                                                <img src="{{ asset("images/profile.jpg") }}" alt="Avatar" />
                                            @else
                                                {{ strtoupper(substr($u->name, 0, 1)) }}
                                            @endif
                                        </div>
                                        <span class="fw-semibold text-dark">{{ $u->name }}</span>
                                    </div>
                                </td>
                                <td>{{ $u->email }}</td>
                                <td>{{ $u->no_hp }}</td>
                                <td>
                                    @if ($u->role === "admin")
                                        <span
                                            class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-20 px-2 py-1">
                                            <i class="bi bi-shield-lock me-1"></i>Admin
                                        </span>
                                    @elseif ($u->role === "petugas")
                                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-20 px-2 py-1">
                                            <i class="bi bi-person-badge me-1"></i>Petugas
                                        </span>
                                    @else
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-20 px-2 py-1">
                                            <i class="bi bi-person me-1"></i>Pengguna
                                        </span>
                                    @endif

                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex gap-1 justify-content-end align-items-center ">
                                        <a href="{{ route("user.edit", $u->id) }}" class="btn btn-sm btn-outline-primary me-1" title="Edit Kategori">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </a>
                                        <form action="{{ route("user.destroy", $u->id) }}" class="mb-0" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus item ini?')">
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
            <nav>
                <ul class="pagination pagination-sm justify-content-end mb-0">
                    <li class="page-item disabled">
                        <a class="page-link" href="#" tabindex="-1">Previous</a>
                    </li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item">
                        <a class="page-link" href="#">Next</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
@endsection

@section('script')

@endsection
