@extends('layouts.admin')

@section('title', 'Transaksi Booking')

@section('style')
<style>
    .user-avatar-sm {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 0.85rem;
    }
</style>
@endsection

@section('main')
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="bi bi-journal-check me-2 text-primary"></i>Transaksi Booking
            </h4>
            <p class="text-muted mb-0">Kelola transaksi booking - On Rent, Pembayaran, Pengembalian</p>
        </div>
    </div>

    <!-- Statistik Cards -->
    <div class="row row-cols-1 row-cols-md-5 g-3 mb-4">
        <div class="col">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-warning bg-opacity-10 p-3">
                        <i class="bi bi-clock-fill text-warning fs-4"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 small">Pending</h6>
                        <h3 class="fw-bold mb-0">{{ $statPending }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-info bg-opacity-10 p-3">
                        <i class="bi bi-check-circle-fill text-info fs-4"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 small">Approved</h6>
                        <h3 class="fw-bold mb-0">{{ $statApproved }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-primary bg-opacity-10 p-3">
                        <i class="bi bi-truck text-primary fs-4"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 small">On Rent</h6>
                        <h3 class="fw-bold mb-0">{{ $statOnRent }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-success bg-opacity-10 p-3">
                        <i class="bi bi-check2-all text-success fs-4"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 small">Completed</h6>
                        <h3 class="fw-bold mb-0">{{ $statCompleted }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-danger bg-opacity-10 p-3">
                        <i class="bi bi-x-circle-fill text-danger fs-4"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 small">Cancelled</h6>
                        <h3 class="fw-bold mb-0">{{ $statCancelled }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body">
            <form action="{{ route('adminPenyewaan.list') }}" method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Cari</label>
                        <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Kode atau nama...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="padding" {{ request('status') == 'padding' ? 'selected' : '' }}>⏳ Pending</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>✅ Approved</option>
                            <option value="rented" {{ request('status') == 'rented' ? 'selected' : '' }}>🚗 On Rent</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>✔️ Completed</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>❌ Cancelled</option>
                            <option value="declined" {{ request('status') == 'declined' ? 'selected' : '' }}>⛔ Declined</option>
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
                            <a href="{{ route('adminPenyewaan.list') }}" class="btn btn-outline-secondary" title="Reset Filter">
                                <i class="bi bi-arrow-clockwise"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Table Booking -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Kode Booking</th>
                            <th>Customer</th>
                            <th>Tanggal Sewa</th>
                            <th>Total Biaya</th>
                            <th>Status</th>
                            <th>Pembayaran</th>
                            <th class="text-center pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($penyewaans as $item)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-primary font-monospace">{{ $item->kode_penyewaan }}</div>
                                    <small class="text-muted">{{ $item->created_at->format('d M Y H:i') }}</small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="user-avatar-sm bg-primary bg-opacity-10 text-primary">
                                            {{ strtoupper(substr($item->customer_name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $item->customer_name }}</div>
                                            <small class="text-muted">{{ $item->user->email ?? '-' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <small>
                                            <i class="bi bi-calendar-check text-success me-1"></i>
                                            {{ \Carbon\Carbon::parse($item->pickup_date)->format('d M Y') }}
                                        </small>
                                        <small>
                                            <i class="bi bi-calendar-x text-danger me-1"></i>
                                            {{ \Carbon\Carbon::parse($item->return_date)->format('d M Y') }}
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-bold">Rp {{ number_format($item->total_price, 0, ',', '.') }}</span>
                                </td>
                                <td>
                                    @php
                                        $statusClass = [
                                            'padding' => 'bg-warning text-dark',
                                            'approved' => 'bg-info text-dark',
                                            'rented' => 'bg-primary text-white',
                                            'completed' => 'bg-success text-white',
                                            'cancelled' => 'bg-danger text-white',
                                            'declined' => 'bg-secondary text-white',
                                        ][$item->status] ?? 'bg-secondary text-white';
                                    @endphp
                                    <span class="badge {{ $statusClass }} px-3 py-2 rounded-pill">
                                        {{ ucfirst($item->status) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $item->payment_status == 'paid' ? 'bg-success' : 'bg-secondary' }} rounded-pill px-3 py-2">
                                        {{ ucfirst($item->payment_status) }}
                                    </span>
                                </td>
                                <td class="text-center pe-4">
                                    <div class="d-flex gap-1 justify-content-center">
                                        <!-- Admin hanya lihat detail -->
                                        <a href="#" class="btn btn-sm btn-outline-primary rounded-3" title="Lihat Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    Tidak ada data penyewaan ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top">
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    Menampilkan {{ $penyewaans->firstItem() ?? 0 }} - {{ $penyewaans->lastItem() ?? 0 }} dari {{ $penyewaans->total() }} data
                </small>
                <div>
                    {{ $penyewaans->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
@endsection
