@extends('layouts.admin')

@section('title', 'Denda - Petugas')

@section('style')
<style>
    .stat-card {
        border: none;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        border-radius: 12px;
        transition: transform .15s ease, box-shadow .15s ease;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 14px rgba(0,0,0,0.1);
    }
    .user-avatar-sm {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.95rem;
    }
    .denda-table table thead th {
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: .3px;
        color: #6c757d;
        font-weight: 600;
        border-bottom: 2px solid #eef1f4;
        white-space: nowrap;
    }
    .denda-table table tbody td {
        vertical-align: middle;
        font-size: 0.9rem;
    }
</style>
@endsection

@section('main')
<div class="container-fluid py-2">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="bi bi-exclamation-triangle text-danger me-2"></i>Daftar Denda
            </h4>
            <p class="text-muted mb-0">Kelola denda dari pengembalian yang ditolak.</p>
        </div>
    </div>

    {{-- Statistik --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-4 col-md-4 col-6">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-warning bg-opacity-10 p-3">
                        <i class="bi bi-hourglass-split text-warning fs-4"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Belum Dibayar</h6>
                        <h3 class="fw-bold mb-0">{{ $stats['belum'] }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-4 col-6">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-success bg-opacity-10 p-3">
                        <i class="bi bi-check-circle-fill text-success fs-4"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Sudah Dibayar</h6>
                        <h3 class="fw-bold mb-0">{{ $stats['sudah'] }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-4 col-12">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-danger bg-opacity-10 p-3">
                        <i class="bi bi-cash-stack text-danger fs-4"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Total Denda</h6>
                        <h5 class="fw-bold mb-0 text-danger">
                            Rp {{ number_format($stats['total'], 0, ',', '.') }}
                        </h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body">
            <form action="{{ route('petugas.denda.list') }}" method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label small text-muted">Cari</label>
                        <input type="text" name="search" class="form-control"
                               placeholder="Kode denda / booking / nama..."
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small text-muted">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Semua</option>
                            <option value="belum dibayar" {{ request('status') === 'belum dibayar' ? 'selected' : '' }}>
                                ⏳ Belum Dibayar
                            </option>
                            <option value="sudah dibayar" {{ request('status') === 'sudah dibayar' ? 'selected' : '' }}>
                                ✅ Sudah Dibayar
                            </option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1">
                                <i class="bi bi-funnel me-1"></i>Filter
                            </button>
                            <a href="{{ route('petugas.denda.list') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-clockwise"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabel --}}
    <div class="card border-0 shadow-sm rounded-3 denda-table">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Kode Denda</th>
                            <th>Booking</th>
                            <th>Peminjam</th>
                            <th>Nominal</th>
                            <th>Status</th>
                            <th>Bukti</th>
                            <th class="text-center pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($dendas as $d)
                            @php
                                $initial = strtoupper(substr($d->user->name ?? 'X', 0, 1));
                            @endphp
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-primary font-monospace">
                                        {{ $d->kode_denda }}
                                    </div>
                                    <small class="text-muted">
                                        {{ optional($d->created_at)->format('d M Y H:i') }}
                                    </small>
                                </td>

                                <td>
                                    <div class="fw-semibold">
                                        {{ $d->penyewaan->kode_penyewaan ?? '-' }}
                                    </div>
                                    <small class="text-muted">
                                        {{ $d->penyewaan->customer_name ?? '-' }}
                                    </small>
                                </td>

                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="user-avatar-sm bg-primary bg-opacity-10 text-primary">
                                            {{ $initial }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold small">
                                                {{ $d->user->name ?? '-' }}
                                            </div>
                                            <small class="text-muted">
                                                {{ $d->user->email ?? '-' }}
                                            </small>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <span class="fw-bold text-danger">
                                        Rp {{ number_format($d->nominal, 0, ',', '.') }}
                                    </span>
                                </td>

                                <td>
                                    @if ($d->status === 'sudah dibayar')
                                        <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">
                                            <i class="bi bi-check-circle me-1"></i>Sudah Dibayar
                                        </span>
                                    @else
                                        <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill">
                                            <i class="bi bi-hourglass-split me-1"></i>Belum Dibayar
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    @if ($d->bukti_dibayar)
                                        <a href="{{ asset('uploads/denda/bukti/' . $d->bukti_dibayar) }}"
                                           target="_blank"
                                           class="badge bg-info text-decoration-none">
                                            <i class="bi bi-image me-1"></i>Lihat
                                        </a>
                                    @else
                                        <span class="text-muted small fst-italic">Tidak ada</span>
                                    @endif
                                </td>

                                <td class="text-center pe-4">
                                    <a href="{{ route('petugas.denda.setting', $d->id) }}"
                                       class="btn btn-sm btn-outline-primary rounded-3"
                                       title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                                    <h6 class="fw-bold mt-3 mb-1">Belum Ada Data Denda</h6>
                                    <p class="text-muted small mb-0">
                                        Tidak ada denda yang cocok dengan filter Anda.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($dendas->count() > 0)
            <div class="card-footer bg-white border-top">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <small class="text-muted">
                        Menampilkan {{ $dendas->firstItem() }} - {{ $dendas->lastItem() }}
                        dari {{ $dendas->total() }} data
                    </small>
                    <div>
                        {{ $dendas->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
