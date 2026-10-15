@extends('layouts.admin')

@section('title', 'Laporan Penyewaan - Petugas')

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
    .report-table table thead th {
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: .3px;
        color: #6c757d;
        font-weight: 600;
        border-bottom: 2px solid #eef1f4;
        white-space: nowrap;
    }
    .report-table table tbody td {
        vertical-align: middle;
        font-size: 0.85rem;
    }
</style>
@endsection

@section('main')
<div class="container-fluid py-2">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="bi bi-file-earmark-bar-graph text-primary me-2"></i>Laporan Penyewaan
            </h4>
            <p class="text-muted mb-0">
                Laporan pendapatan sewa dan denda dalam periode tertentu.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('petugas.report.export', ['date_from' => $dateFrom, 'date_to' => $dateTo, 'status' => $status]) }}"
               class="btn btn-success rounded-3">
                <i class="bi bi-file-earmark-excel me-1"></i>Export Excel
            </a>
        </div>
    </div>

    {{-- Flash --}}
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle me-1"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Filter --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body">
            <form action="{{ route('petugas.report.list') }}" method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Dari Tanggal</label>
                        <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Sampai Tanggal</label>
                        <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            @foreach ([
                                'padding'   => 'Pending',
                                'approved'  => 'Approved',
                                'rented'    => 'On Rent',
                                'checking'  => 'Checking',
                                'denda'     => 'Denda',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                                'declined'  => 'Declined',
                            ] as $val => $label)
                                <option value="{{ $val }}" {{ $status === $val ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1">
                                <i class="bi bi-funnel me-1"></i>Tampilkan
                            </button>
                            <a href="{{ route('petugas.report.list') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-clockwise"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Statistik --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-md-6 col-6">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 bg-primary bg-opacity-10 p-3">
                            <i class="bi bi-journal-check text-primary fs-4"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Total Booking</h6>
                            <h3 class="fw-bold mb-0">{{ $summary['total_booking'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-6">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 bg-success bg-opacity-10 p-3">
                            <i class="bi bi-check2-all text-success fs-4"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Selesai</h6>
                            <h3 class="fw-bold mb-0">{{ $summary['total_completed'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-6">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 bg-danger bg-opacity-10 p-3">
                            <i class="bi bi-x-circle-fill text-danger fs-4"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Dibatalkan</h6>
                            <h3 class="fw-bold mb-0">{{ $summary['total_cancelled'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-6">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 bg-warning bg-opacity-10 p-3">
                            <i class="bi bi-exclamation-triangle text-warning fs-4"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Ada Denda</h6>
                            <h3 class="fw-bold mb-0">{{ $summary['total_denda'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Keuangan --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-4 col-md-6">
            <div class="card stat-card h-100 border-start border-success border-4">
                <div class="card-body">
                    <h6 class="text-muted mb-1">
                        <i class="bi bi-cash-stack text-success me-1"></i>Total Pendapatan Sewa
                    </h6>
                    <h4 class="fw-bold mb-0 text-success">
                        Rp {{ number_format($summary['total_pendapatan'], 0, ',', '.') }}
                    </h4>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="card stat-card h-100 border-start border-danger border-4">
                <div class="card-body">
                    <h6 class="text-muted mb-1">
                        <i class="bi bi-exclamation-triangle text-danger me-1"></i>Total Denda
                    </h6>
                    <h4 class="fw-bold mb-0 text-danger">
                        Rp {{ number_format($summary['total_nominal_denda'], 0, ',', '.') }}
                    </h4>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-12">
            <div class="card stat-card h-100 border-start border-primary border-4">
                <div class="card-body">
                    <h6 class="text-muted mb-1">
                        <i class="bi bi-wallet2 text-primary me-1"></i>Total Pendapatan Bersih
                    </h6>
                    <h4 class="fw-bold mb-0 text-primary">
                        Rp {{ number_format($summary['total_bersih'], 0, ',', '.') }}
                    </h4>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel Detail --}}
    <div class="card border-0 shadow-sm rounded-3 report-table">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">
                <i class="bi bi-list-ul me-2 text-primary"></i>Detail Transaksi
            </h5>
            <small class="text-muted">
                Periode: {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }}
                - {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}
            </small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Kode Booking</th>
                            <th>Tanggal</th>
                            <th>Peminjam</th>
                            <th>Kendaraan</th>
                            <th>Durasi</th>
                            <th>Status</th>
                            <th>Pembayaran</th>
                            <th>Pendapatan</th>
                            <th class="pe-4">Denda</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($penyewaans as $p)
                            @php
                                $denda = \App\Models\Denda::where("penyewaan_id", $p->id)->first();
                                $peralatanList = $p->detailPenyewaans->map(fn($d) => $d->peralatan->nama_barang ?? '-')->take(2);
                                $sisa = $p->detailPenyewaans->count() - 2;
                                $statusMap = [
                                    'padding'   => ['label' => 'Pending',   'class' => 'bg-warning bg-opacity-10 text-warning'],
                                    'approved'  => ['label' => 'Approved',  'class' => 'bg-info bg-opacity-10 text-info'],
                                    'rented'    => ['label' => 'On Rent',   'class' => 'bg-primary bg-opacity-10 text-primary'],
                                    'checking'  => ['label' => 'Checking',  'class' => 'bg-secondary bg-opacity-10 text-secondary'],
                                    'denda'     => ['label' => 'Denda',     'class' => 'bg-danger bg-opacity-10 text-danger'],
                                    'completed' => ['label' => 'Completed', 'class' => 'bg-success bg-opacity-10 text-success'],
                                    'cancelled' => ['label' => 'Cancelled', 'class' => 'bg-danger bg-opacity-10 text-danger'],
                                    'declined'  => ['label' => 'Declined',  'class' => 'bg-danger bg-opacity-10 text-danger'],
                                ];
                                $st = $statusMap[$p->status] ?? ['label' => $p->status, 'class' => 'bg-secondary'];
                            @endphp
                            <tr>
                                <td class="ps-4">
                                    <span class="fw-bold font-monospace text-primary">
                                        {{ $p->kode_penyewaan }}
                                    </span>
                                </td>
                                <td>
                                    <small>{{ optional($p->created_at)->format('d M Y') }}</small>
                                </td>
                                <td>
                                    <div class="fw-semibold small">{{ $p->customer_name }}</div>
                                    <small class="text-muted">{{ $p->customer_id_card }}</small>
                                </td>
                                <td>
                                    <small>{{ $peralatanList->implode(', ') }}</small>
                                    @if ($sisa > 0)
                                        <small class="text-muted">+{{ $sisa }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">
                                        {{ $p->detailPenyewaans->first()->durasi_hari ?? 0 }} hari
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $st['class'] }} rounded-pill px-3 py-2">
                                        {{ $st['label'] }}
                                    </span>
                                </td>
                                <td>
                                    @if ($p->payment_status === 'paid')
                                        <span class="badge bg-success rounded-pill">Lunas</span>
                                    @else
                                        <span class="badge bg-secondary rounded-pill">Unpaid</span>
                                    @endif
                                </td>
                                <td class="fw-bold text-success">
                                    Rp {{ number_format($p->payment_status === 'paid' ? $p->total_price : 0, 0, ',', '.') }}
                                </td>
                                <td class="pe-4">
                                    @if ($denda)
                                        <div class="fw-bold text-danger small">
                                            Rp {{ number_format($denda->nominal, 0, ',', '.') }}
                                        </div>
                                        <small class="badge bg-{{ $denda->status === 'sudah dibayar' ? 'success' : 'warning text-dark' }}">
                                            {{ $denda->status === 'sudah dibayar' ? 'Dibayar' : 'Belum' }}
                                        </small>
                                    @else
                                        <span class="text-muted small fst-italic">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                                    <h6 class="fw-bold mt-3 mb-1">Tidak Ada Data</h6>
                                    <p class="text-muted small mb-0">
                                        Tidak ada transaksi pada periode ini.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection