@extends('layouts.pengguna')

@section('title', 'Status & Riwayat Booking')

@section('style')
<style>
    .booking-card {
        border: none;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        border-radius: 12px;
        margin-bottom: 16px;
        overflow: hidden;
    }
    .booking-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.12);
    }
    .info-label {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        color: #6c757d;
        font-weight: 600;
    }
    .info-value {
        font-size: 0.9rem;
        font-weight: 600;
        color: #212529;
    }
    .detail-item {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 8px 12px;
        margin-bottom: 6px;
    }
    .detail-item:last-child { margin-bottom: 0; }
</style>
@endsection

@section('main')
<div class="container mt-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="bi bi-journal-check text-primary me-2"></i>Status & Riwayat Penyewaan
            </h4>
            <p class="text-muted small mb-0">
                Pantau status persetujuan dan riwayat penyewaan Anda di sini.
            </p>
        </div>
        <a href="{{ route('katalog.list') ?? '#' }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Sewa Kendaraan Lain
        </a>
    </div>

    {{-- Daftar Booking --}}
    <div class="d-flex flex-column gap-3">
        @forelse ($penyewaans as $item)
            @php
                $statusMap = [
                    'padding'   => ['label' => 'Menunggu Konfirmasi',      'class' => 'bg-warning text-dark', 'icon' => 'bi-clock'],
                    'approved'  => ['label' => 'Disetujui',                'class' => 'bg-info text-dark',    'icon' => 'bi-hand-thumbs-up'],
                    'rented'    => ['label' => 'Sedang Disewa',            'class' => 'bg-primary',           'icon' => 'bi-car-front'],
                    'checking'  => ['label' => 'Verifikasi Pengembalian',  'class' => 'bg-secondary',         'icon' => 'bi-search'],
                    'denda'     => ['label' => 'Ada Denda',                'class' => 'bg-danger',            'icon' => 'bi-exclamation-triangle'],
                    'completed' => ['label' => 'Selesai',                  'class' => 'bg-success',           'icon' => 'bi-check-circle'],
                    'cancelled' => ['label' => 'Dibatalkan',               'class' => 'bg-danger',            'icon' => 'bi-x-circle'],
                    'declined'  => ['label' => 'Ditolak',                  'class' => 'bg-danger',            'icon' => 'bi-x-octagon'],
                ];
                $paymentMap = [
                    'unpaid' => ['label' => 'Belum Bayar', 'class' => 'bg-danger bg-opacity-10 text-danger border border-danger border-opacity-20', 'icon' => 'bi-hourglass-split'],
                    'paid'   => ['label' => 'Lunas',       'class' => 'bg-success bg-opacity-10 text-success border border-success border-opacity-20', 'icon' => 'bi-cash-stack'],
                ];

                $st  = $statusMap[$item->status] ?? ['label' => $item->status, 'class' => 'bg-secondary', 'icon' => 'bi-question'];
                $pay = $paymentMap[$item->payment_status] ?? ['label' => $item->payment_status, 'class' => 'bg-secondary', 'icon' => 'bi-question'];

                // Durasi hari
                $pickup = \Carbon\Carbon::parse($item->pickup_date);
                $return = \Carbon\Carbon::parse($item->return_date);
                $durasi = $pickup->diffInDays($return) ?: 1;

                $details    = $item->detailPenyewaans;
                $totalItem  = $details->count();
            @endphp

            <div class="card booking-card">
                {{-- ============ HEADER: Kode Transaksi + Status ============ --}}
                <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <span class="text-muted small d-block">Kode Transaksi</span>
                        <span class="fw-bold font-monospace text-dark fs-6">{{ $item->kode_penyewaan }}</span>
                        <small class="text-muted ms-2">
                            ({{ optional($item->created_at)->format('d M Y, H:i') }})
                        </small>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <span class="badge {{ $st['class'] }} px-3 py-2">
                            <i class="bi {{ $st['icon'] }} me-1"></i> {{ $st['label'] }}
                        </span>
                        <span class="badge {{ $pay['class'] }} px-3 py-2">
                            <i class="bi {{ $pay['icon'] }} me-1"></i> {{ $pay['label'] }}
                        </span>
                    </div>
                </div>

                {{-- ============ BODY ============ --}}
                <div class="card-body">

                    {{-- === Baris 1: Nama Peminjam + Kontak + Tanggal + Total === --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-3 col-6">
                            <div class="info-label">Nama Peminjam</div>
                            <div class="info-value">
                                <i class="bi bi-person-circle text-primary me-1"></i>
                                {{ $item->customer_name ?? '-' }}
                            </div>
                        </div>

                        <div class="col-md-3 col-6">
                            <div class="info-label">No. Identitas (KTP)</div>
                            <div class="info-value">
                                <i class="bi bi-credit-card-2-front text-primary me-1"></i>
                                {{ $item->customer_id_card ?? '-' }}
                            </div>
                        </div>

                        <div class="col-md-3 col-6">
                            <div class="info-label">Tanggal Pengambilan</div>
                            <div class="info-value">
                                <i class="bi bi-calendar-event text-primary me-1"></i>
                                {{ $pickup->format('d M Y') }}
                            </div>
                        </div>

                        <div class="col-md-3 col-6">
                            <div class="info-label">Tanggal Pengembalian</div>
                            <div class="info-value">
                                <i class="bi bi-calendar-check text-primary me-1"></i>
                                {{ $return->format('d M Y') }}
                                <small class="text-muted">({{ $durasi }} hari)</small>
                            </div>
                        </div>
                    </div>

                    {{-- === Baris 2: Alamat === --}}
                    <div class="row g-3 mb-3">
                        <div class="col-12">
                            <div class="info-label">Alamat Peminjam</div>
                            <div class="info-value fw-normal">
                                <i class="bi bi-geo-alt text-primary me-1"></i>
                                {{ $item->customer_address ?? '-' }}
                            </div>
                        </div>
                    </div>

                    {{-- === Baris 3: Detail Peralatan === --}}
                    <div class="info-label mb-2">
                        <i class="bi bi-box-seam me-1"></i>
                        Detail Peralatan ({{ $totalItem }} item)
                    </div>

                    @if ($totalItem > 0)
                        <div class="row g-2 mb-3">
                            @foreach ($details as $d)
                                @php
                                    $alat = $d->peralatan;
                                @endphp
                                <div class="col-md-6">
                                    <div class="detail-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="fw-semibold small">
                                                {{ $alat->nama_barang ?? 'Peralatan tidak ditemukan' }}
                                            </div>
                                            <div class="text-muted" style="font-size: 0.75rem;">
                                                <span class="font-monospace">{{ $alat->kode_barang ?? '-' }}</span>
                                                &middot;
                                                Rp {{ number_format($d->price_per_day, 0, ',', '.') }}/hari
                                                &middot;
                                                {{ $d->jumlah }} unit
                                                &middot;
                                                {{ $d->durasi_hari }} hari
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-bold text-primary small">
                                                Rp {{ number_format($d->subtotal, 0, ',', '.') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-light border small mb-3">
                            <i class="bi bi-info-circle me-1"></i>
                            Tidak ada detail peralatan pada transaksi ini.
                        </div>
                    @endif

                    {{-- === Baris 4: Total + Aksi === --}}
                    <div class="row align-items-center g-3 pt-3 border-top">
                        <div class="col-md-6 col-12">
                            <span class="info-label d-block">Total Biaya</span>
                            <h5 class="fw-bold text-primary mb-0">
                                Rp {{ number_format($item->total_price, 0, ',', '.') }}
                            </h5>
                        </div>

                        <div class="col-md-6 col-12 text-md-end">
                            @if ($item->status === 'padding')
                                {{-- Batalkan dengan confirm() biasa, TANPA @method('DELETE') --}}
                                <form action="{{ route('booking.cancel', $item->id) }}"
                                      method="POST"
                                      class="d-inline"
                                      onsubmit="return confirm('Yakin ingin membatalkan booking {{ $item->kode_penyewaan }}?');">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-danger btn-sm">
                                        <i class="bi bi-x-circle me-1"></i> Batalkan
                                    </button>
                                </form>

                            @elseif ($item->status === 'approved')
                                @if ($item->payment_status === 'unpaid')
                                    <a href="{{ route('booking.qr', $item->id) }}" class="btn btn-primary btn-sm">
                                        <i class="bi bi-cash-coin me-1"></i> Bayar Sekarang
                                    </a>
                                @else
                                    <span class="text-muted small">
                                        <i class="bi bi-lock me-1"></i>Menunggu Petugas
                                    </span>
                                @endif

                            @elseif ($item->status === 'rented')
                                <a href="{{ route('pengembalian.form', $item->id) }}" class="btn btn-success btn-sm">
                                    <i class="bi bi-arrow-return-left me-1"></i> Kembalikan
                                </a>

                            @elseif ($item->status === 'checking')
                                <span class="text-muted small">
                                    <i class="bi bi-search me-1"></i>Verifikasi Petugas
                                </span>

                            @elseif ($item->status === 'denda')
                                @php
                                    // Ambil denda terkait
                                    $dendaItem = \App\Models\Denda::where('penyewaan_id', $item->id)->first();
                                @endphp

                                @if ($dendaItem)
                                    @if ($dendaItem->status === 'belum dibayar')
                                        @if ($dendaItem->bukti_dibayar)
                                            <span class="text-muted small">
                                                <i class="bi bi-hourglass-split me-1"></i>Menunggu Konfirmasi Petugas
                                            </span>
                                        @else
                                            <a href="{{ route('denda.form', $dendaItem->id) }}"
                                            class="btn btn-danger btn-sm">
                                                <i class="bi bi-cash-coin me-1"></i>Bayar Denda
                                            </a>
                                        @endif
                                    @else
                                        <span class="text-muted small">
                                            <i class="bi bi-check-circle me-1"></i>Denda Lunas
                                        </span>
                                    @endif
                                @else
                                    <span class="text-muted small">
                                        <i class="bi bi-hourglass-split me-1"></i>Menunggu Denda
                                    </span>
                                @endif
                            @else
                                <span class="text-muted small">
                                    <i class="bi bi-lock me-1"></i>{{ $st['label'] }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            {{-- Empty State --}}
            <div class="card booking-card text-center py-5">
                <div class="card-body">
                    <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
                    <h6 class="fw-bold mt-3">Belum Ada Riwayat Penyewaan</h6>
                    <p class="text-muted small">Anda belum pernah melakukan pengajuan penyewaan kendaraan.</p>
                    <a href="{{ route('katalog.list') ?? '#' }}" class="btn btn-primary btn-sm mt-2">
                        <i class="bi bi-car-front me-1"></i> Mulai Sewa Kendaraan
                    </a>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection
