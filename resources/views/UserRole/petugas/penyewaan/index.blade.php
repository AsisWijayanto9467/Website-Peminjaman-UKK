@extends('layouts.admin')

@section('title', 'Transaksi Booking - Petugas')

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

    {{-- ================= Header ================= --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="bi bi-journal-check me-2 text-primary"></i>Transaksi Booking
            </h4>
            <p class="text-muted mb-0">Kelola seluruh transaksi booking penyewaan pengguna.</p>
        </div>
    </div>

    {{-- ================= Statistik Cards ================= --}}
    <div class="row g-3 mb-4">
        <div class="col-lg col-md-4 col-6">
            <div class="card stat-card-booking h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-warning bg-opacity-10 p-3">
                        <i class="bi bi-clock-fill text-warning fs-4"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Pending</h6>
                        <h3 class="fw-bold mb-0">{{ $stats['padding'] }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg col-md-4 col-6">
            <div class="card stat-card-booking h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-info bg-opacity-10 p-3">
                        <i class="bi bi-check-circle-fill text-info fs-4"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Approved</h6>
                        <h3 class="fw-bold mb-0">{{ $stats['approved'] }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg col-md-4 col-6">
            <div class="card stat-card-booking h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-primary bg-opacity-10 p-3">
                        <i class="bi bi-truck text-primary fs-4"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">On Rent</h6>
                        <h3 class="fw-bold mb-0">{{ $stats['rented'] }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg col-md-6 col-6">
            <div class="card stat-card-booking h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-success bg-opacity-10 p-3">
                        <i class="bi bi-check2-all text-success fs-4"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Completed</h6>
                        <h3 class="fw-bold mb-0">{{ $stats['completed'] }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg col-md-6 col-6">
            <div class="card stat-card-booking h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-danger bg-opacity-10 p-3">
                        <i class="bi bi-x-circle-fill text-danger fs-4"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Cancelled</h6>
                        <h3 class="fw-bold mb-0">{{ $stats['cancelled'] }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= Filter ================= --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body">
            <form action="{{ route('petugas.penyewaan.list') }}" method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Cari</label>
                        <input type="text" name="search" class="form-control"
                               placeholder="Kode / nama / KTP..."
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Semua</option>
                            @foreach ([
                                'padding'   => '⏳ Menunggu Konfirmasi',
                                'approved'  => '✅ Disetujui',
                                'rented'    => '🚗 Sedang Disewa',
                                'checking'  => '🔎 Verifikasi Pengembalian',
                                'denda'     => '⚠️ Ada Denda',
                                'completed' => '✔️ Selesai',
                                'cancelled' => '❌ Dibatalkan',
                                'declined'  => '🚫 Ditolak',
                            ] as $val => $label)
                                <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
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
                            <a href="{{ route('petugas.penyewaan.list') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-clockwise"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ================= Tabel ================= --}}
    <div class="card border-0 shadow-sm rounded-3 booking-table">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Kode Booking</th>
                            <th>Customer</th>
                            <th>Kendaraan</th>
                            <th>Tanggal Sewa</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Pembayaran</th>
                            <th class="text-center pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($penyewaans as $item)
                            @php
                                $statusMap = [
                                    'padding'   => ['label' => 'Pending',   'class' => 'bg-warning bg-opacity-10 text-warning', 'icon' => 'bi-clock'],
                                    'approved'  => ['label' => 'Approved',  'class' => 'bg-info bg-opacity-10 text-info',       'icon' => 'bi-check-circle'],
                                    'rented'    => ['label' => 'On Rent',   'class' => 'bg-primary bg-opacity-10 text-primary', 'icon' => 'bi-truck'],
                                    'checking'  => ['label' => 'Checking',  'class' => 'bg-secondary bg-opacity-10 text-secondary', 'icon' => 'bi-search'],
                                    'denda'     => ['label' => 'Denda',     'class' => 'bg-danger bg-opacity-10 text-danger',   'icon' => 'bi-exclamation-triangle'],
                                    'completed' => ['label' => 'Completed', 'class' => 'bg-success bg-opacity-10 text-success', 'icon' => 'bi-check2-all'],
                                    'cancelled' => ['label' => 'Cancelled', 'class' => 'bg-danger bg-opacity-10 text-danger',   'icon' => 'bi-x-circle'],
                                    'declined'  => ['label' => 'Declined',  'class' => 'bg-danger bg-opacity-10 text-danger',   'icon' => 'bi-x-octagon'],
                                ];
                                $st = $statusMap[$item->status] ?? ['label' => $item->status, 'class' => 'bg-secondary', 'icon' => 'bi-question'];

                                $pickup = \Carbon\Carbon::parse($item->pickup_date);
                                $return = \Carbon\Carbon::parse($item->return_date);

                                $firstDetail = $item->detailPenyewaans->first();
                                $peralatan   = $firstDetail->peralatan ?? null;
                                $totalItem   = $item->detailPenyewaans->count();

                                $initial = strtoupper(substr($item->customer_name ?? 'X', 0, 1));
                            @endphp
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-primary">{{ $item->kode_penyewaan }}</div>
                                    <small class="text-muted">
                                        {{ optional($item->created_at)->format('d M Y H:i') }}
                                    </small>
                                </td>

                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="user-avatar-sm bg-primary bg-opacity-10 text-primary">
                                            {{ $initial }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $item->customer_name }}</div>
                                            <small class="text-muted">
                                                {{ $item->user->email ?? '-' }}
                                            </small>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <div class="fw-semibold">
                                        {{ $peralatan->nama_barang ?? '-' }}
                                        @if ($totalItem > 1)
                                            <small class="text-muted">+{{ $totalItem - 1 }}</small>
                                        @endif
                                    </div>
                                    <small class="text-muted font-monospace">
                                        {{ $peralatan->kode_barang ?? '-' }}
                                    </small>
                                </td>

                                <td>
                                    <div class="d-flex flex-column">
                                        <small>
                                            <i class="bi bi-calendar-check text-success me-1"></i>
                                            {{ $pickup->format('d M Y') }}
                                        </small>
                                        <small>
                                            <i class="bi bi-calendar-x text-danger me-1"></i>
                                            {{ $return->format('d M Y') }}
                                        </small>
                                    </div>
                                </td>

                                <td>
                                    <span class="fw-bold">
                                        Rp {{ number_format($item->total_price, 0, ',', '.') }}
                                    </span>
                                </td>

                                <td>
                                    <span class="badge {{ $st['class'] }} px-3 py-2 rounded-pill">
                                        <i class="bi {{ $st['icon'] }} me-1"></i>{{ $st['label'] }}
                                    </span>
                                </td>

                                <td>
                                    @if ($item->payment_status === 'paid')
                                        <span class="badge bg-success rounded-pill px-3 py-2">Lunas</span>
                                    @else
                                        <span class="badge bg-secondary rounded-pill px-3 py-2">Unpaid</span>
                                    @endif
                                </td>

                                <td class="text-center pe-4">
                                    <div class="d-flex gap-1 justify-content-center">
                                        {{-- Tombol Detail selalu tampil --}}
                                        <a href="{{ route('petugas.penyewaan.setting', $item->id) }}"
                                           class="btn btn-sm btn-outline-primary rounded-3"
                                           title="Kelola / Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        {{-- Tombol Hapus hanya tampil jika status = completed --}}
                                        @if ($item->status === 'completed')
                                            <form action="{{ route('petugas.penyewaan.destroy', $item->id) }}"
                                                  method="POST"
                                                  class="d-inline"
                                                  onsubmit="return confirm('Yakin ingin menghapus permanen data booking {{ $item->kode_penyewaan }}? Tindakan ini tidak bisa dibatalkan.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-3"
                                                        title="Hapus Permanen">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                                    <h6 class="fw-bold mt-3 mb-1">Belum Ada Data Booking</h6>
                                    <p class="text-muted small mb-0">
                                        Tidak ada transaksi penyewaan yang cocok dengan filter Anda.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ================= Pagination ================= --}}
        @if ($penyewaans->count() > 0)
            <div class="card-footer bg-white border-top">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <small class="text-muted">
                        Menampilkan {{ $penyewaans->firstItem() }} - {{ $penyewaans->lastItem() }}
                        dari {{ $penyewaans->total() }} data
                    </small>
                    <div>
                        {{ $penyewaans->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@section('script')
{{-- Tidak perlu JS tambahan karena pakai confirm() bawaan browser --}}
@endsection
