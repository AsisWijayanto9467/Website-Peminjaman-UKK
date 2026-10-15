@extends('layouts.admin')

@section('title', 'Pengembalian Barang - Petugas')

@section('style')
<style>
    .stat-card-booking {
        border: none;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        border-radius: 12px;
        transition: transform .15s ease, box-shadow .15s ease;
    }
    .stat-card-booking:hover {
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
    .booking-table table thead th {
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: .3px;
        color: #6c757d;
        font-weight: 600;
        border-bottom: 2px solid #eef1f4;
        white-space: nowrap;
    }
    .booking-table table tbody td {
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
                <i class="bi bi-arrow-return-left me-2 text-primary"></i>Pengembalian Barang
            </h4>
            <p class="text-muted mb-0">Kelola pengajuan pengembalian dari pengguna.</p>
        </div>
    </div>

    {{-- Statistik --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-4 col-md-4 col-6">
            <div class="card stat-card-booking h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-warning bg-opacity-10 p-3">
                        <i class="bi bi-clock-fill text-warning fs-4"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Menunggu</h6>
                        <h3 class="fw-bold mb-0">{{ $stats['padding'] }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-4 col-6">
            <div class="card stat-card-booking h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-success bg-opacity-10 p-3">
                        <i class="bi bi-check-circle-fill text-success fs-4"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Diterima</h6>
                        <h3 class="fw-bold mb-0">{{ $stats['accept'] }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-4 col-6">
            <div class="card stat-card-booking h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-danger bg-opacity-10 p-3">
                        <i class="bi bi-x-circle-fill text-danger fs-4"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Ditolak</h6>
                        <h3 class="fw-bold mb-0">{{ $stats['reject'] }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body">
            <form action="{{ route('petugas.pengembalian.list') }}" method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Cari</label>
                        <input type="text" name="search" class="form-control"
                               placeholder="Kode booking / nama..."
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Semua</option>
                            <option value="padding" {{ request('status') === 'padding' ? 'selected' : '' }}>⏳ Menunggu</option>
                            <option value="accept"  {{ request('status') === 'accept'  ? 'selected' : '' }}>✅ Diterima</option>
                            <option value="reject"  {{ request('status') === 'reject'  ? 'selected' : '' }}>❌ Ditolak</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted">Dari Tgl</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted">Sampai Tgl</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-2">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1">
                                <i class="bi bi-funnel me-1"></i>Filter
                            </button>
                            <a href="{{ route('petugas.pengembalian.list') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-clockwise"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabel --}}
    <div class="card border-0 shadow-sm rounded-3 booking-table">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Kode Booking</th>
                            <th>Customer</th>
                            <th>Tgl Pengajuan</th>
                            <th>Tgl Kembali Aktual</th>
                            <th>Catatan Pengguna</th>
                            <th>Status</th>
                            <th class="text-center pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pengembalians as $p)
                            @php
                                $statusMap = [
                                    'padding' => ['label' => 'Menunggu', 'class' => 'bg-warning bg-opacity-10 text-warning', 'icon' => 'bi-clock'],
                                    'accept'  => ['label' => 'Diterima', 'class' => 'bg-success bg-opacity-10 text-success', 'icon' => 'bi-check-circle'],
                                    'reject'  => ['label' => 'Ditolak',  'class' => 'bg-danger bg-opacity-10 text-danger',   'icon' => 'bi-x-circle'],
                                ];
                                $st = $statusMap[$p->status] ?? ['label' => $p->status, 'class' => 'bg-secondary', 'icon' => 'bi-question'];

                                $penyewaan = $p->penyewaan;
                                $initial   = strtoupper(substr($penyewaan->customer_name ?? 'X', 0, 1));
                            @endphp
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-primary">
                                        {{ $penyewaan->kode_penyewaan ?? '-' }}
                                    </div>
                                    <small class="text-muted">
                                        {{ optional($p->created_at)->format('d M Y H:i') }}
                                    </small>
                                </td>

                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="user-avatar-sm bg-primary bg-opacity-10 text-primary">
                                            {{ $initial }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold">
                                                {{ $penyewaan->customer_name ?? '-' }}
                                            </div>
                                            <small class="text-muted">
                                                {{ $penyewaan->user->email ?? '-' }}
                                            </small>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <small>
                                        <i class="bi bi-calendar-plus text-primary me-1"></i>
                                        {{ optional($p->tanggal_pengajuan)->format('d M Y') ?? '-' }}
                                    </small>
                                </td>

                                <td>
                                    <small>
                                        <i class="bi bi-calendar-check text-success me-1"></i>
                                        {{ optional($p->actual_return_date)->format('d M Y') ?? '-' }}
                                    </small>
                                    @if ($penyewaan)
                                        <small class="text-muted d-block">
                                            Jadwal: {{ \Carbon\Carbon::parse($penyewaan->return_date)->format('d M Y') }}
                                        </small>
                                    @endif
                                </td>

                                <td>
                                    <span class="small text-truncate d-inline-block" style="max-width: 200px;"
                                          title="{{ $p->catatan_pengguna }}">
                                        {{ $p->catatan_pengguna ?? '-' }}
                                    </span>
                                </td>

                                <td>
                                    <span class="badge {{ $st['class'] }} px-3 py-2 rounded-pill">
                                        <i class="bi {{ $st['icon'] }} me-1"></i>{{ $st['label'] }}
                                    </span>
                                </td>

                                <td class="text-center pe-4">
                                    <a href="{{ route('petugas.pengembalian.setting', $p->id) }}"
                                       class="btn btn-sm btn-outline-primary rounded-3"
                                       title="Kelola">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                                    <h6 class="fw-bold mt-3 mb-1">Belum Ada Pengajuan Pengembalian</h6>
                                    <p class="text-muted small mb-0">
                                        Tidak ada pengembalian yang cocok dengan filter Anda.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($pengembalians->count() > 0)
            <div class="card-footer bg-white border-top">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <small class="text-muted">
                        Menampilkan {{ $pengembalians->firstItem() }} - {{ $pengembalians->lastItem() }}
                        dari {{ $pengembalians->total() }} data
                    </small>
                    <div>
                        {{ $pengembalians->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
