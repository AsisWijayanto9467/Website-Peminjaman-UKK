@extends('layouts.admin');

@section('title', 'dashboard')

@section('style')

@endsection

@section('main')
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="bi bi-clock-history me-2 text-primary"></i>Log Aktivitas
            </h4>
            <p class="text-muted mb-0">Catatan semua aktivitas dalam sistem</p>
        </div>
        <div class="d-flex gap-2">
            <a href="#" class="btn btn-outline-secondary rounded-3 px-3">
                <i class="bi bi-arrow-clockwise me-2"></i>Refresh
            </a>
            <button type="button" class="btn btn-danger rounded-3 px-4" data-bs-toggle="modal"
                data-bs-target="#clearLogModal">
                <i class="bi bi-trash me-2"></i>Bersihkan Log Lama
            </button>
        </div>
    </div>

    <!-- Filter -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0 fw-bold"><i class="bi bi-funnel me-2"></i>Filter Log</h5>
        </div>
        <div class="card-body">
            <form action="#" method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">User</label>
                        <select name="user_id" class="form-select">
                            <option value="">Semua User</option>
                            <option value="1">Ahmad Fauzi</option>
                            <option value="2">Budi Santoso</option>
                            <option value="3">Siti Nurhaliza</option>
                            <option value="4">Rudi Hartono</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Role</label>
                        <select name="role" class="form-select">
                            <option value="">Semua Role</option>
                            <option value="admin">Admin</option>
                            <option value="petugas">Petugas</option>
                            <option value="customer">Customer</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Dari Tanggal</label>
                        <input type="date" name="date_from" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Sampai Tanggal</label>
                        <input type="date" name="date_to" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Cari Aktivitas</label>
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Cari aktivitas...">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabel Log Aktivitas -->
    <div class="card border-0 shadow-sm rounded-3 activity-table">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">
                <i class="bi bi-list-ul me-2"></i>Daftar Aktivitas
                <span class="badge bg-primary ms-2 rounded-pill">156</span>
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="5%" class="ps-4">#</th>
                            <th width="25%">User</th>
                            <th width="40%">Aktivitas</th>
                            <th width="15%">Role</th>
                            <th width="15%" class="pe-4">Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($activityLog as $a)
                            <tr>
                                <td class="ps-4 text-muted small">{{ $loop->iteration }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="user-icon-small bg-primary bg-opacity-10">
                                            <i class="bi bi-person-fill text-primary"></i>
                                        </div>
                                        <div>
                                            <span class="fw-medium">{{ $a->user->name ?? "null" }}</span>
                                            <br>
                                            <small class="text-muted">{{ $a->user->email ?? null }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-journal-check text-primary fs-5"></i>
                                        <span>{{ $a->activity }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-primary rounded-pill px-3 py-1">{{ $a->user->role ?? null }}</span>
                                </td>
                                <td class="pe-4">
                                    <div>
                                        <small class="d-block">
                                            <i class="bi bi-calendar3 me-1 text-muted"></i>
                                            {{ $a->created_at }}
                                        </small>
                                        <br>
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
        <div class="card-footer bg-white d-flex justify-content-between align-items-center">
            <small class="text-muted">
                Menampilkan 1 - 8 dari 156 data
            </small>
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
