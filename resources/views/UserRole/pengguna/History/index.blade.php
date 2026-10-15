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
    .vehicle-thumb {
        width: 100%;
        height: 85px;
        object-fit: cover;
        border-radius: 8px;
    }
    .vehicle-thumb-placeholder {
        width: 100%;
        height: 85px;
        border-radius: 8px;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6c757d;
        font-size: 2rem;
    }
</style>
@endsection

@section('main')
<div class="container mt-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-journal-check text-primary me-2"></i>Status & Pengajuan Penyewaan</h4>
            <p class="text-muted small mb-0">Pantau status persetujuan dan riwayat penyewaan kendaraan Anda di sini.</p>
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

                $st = $statusMap[$item->status] ?? ['label' => $item->status, 'class' => 'bg-secondary', 'icon' => 'bi-question'];
                $pay = $paymentMap[$item->payment_status] ?? ['label' => $item->payment_status, 'class' => 'bg-secondary', 'icon' => 'bi-question'];

                // Durasi hari
                $pickup = \Carbon\Carbon::parse($item->pickup_date);
                $return = \Carbon\Carbon::parse($item->return_date);
                $durasi = $pickup->diffInDays($return) ?: 1;

                // Ambil peralatan pertama sebagai preview (kalau ada)
                $firstDetail = $item->detailPenyewaans->first();
                $peralatan = $firstDetail->peralatan ?? null;
                $totalItem = $item->detailPenyewaans->count();
            @endphp

            <div class="card booking-card">
                <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <span class="text-muted small d-block">Kode Booking</span>
                        <span class="fw-bold font-monospace text-dark fs-6">{{ $item->kode_penyewaan }}</span>
                        <small class="text-muted ms-2">({{ $item->created_at->format('d M Y, H:i') }})</small>
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

                <div class="card-body">
                    <div class="row align-items-center g-3">
                        {{-- Thumbnail --}}
                        <div class="col-md-2 col-4">
                            @if ($peralatan && $peralatan->foto)
                                <img src="{{ asset('uploads/peralatan/' . $peralatan->foto) }}" class="vehicle-thumb" alt="{{ $peralatan->name }}">
                            @else
                                <div class="vehicle-thumb-placeholder">
                                    <i class="bi bi-car-front"></i>
                                </div>
                            @endif
                        </div>

                        {{-- Info Peralatan --}}
                        <div class="col-md-3 col-8">
                            <span class="badge bg-secondary mb-1">
                                {{ $peralatan->kategori ?? 'Kendaraan' }}
                            </span>
                            <h6 class="fw-bold mb-1">
                                {{ $peralatan->name ?? 'Tidak ada item' }}
                                @if ($totalItem > 1)
                                    <small class="text-muted">+{{ $totalItem - 1 }} lainnya</small>
                                @endif
                            </h6>
                            <p class="text-muted small mb-0">
                                <i class="bi bi-credit-card-2-front me-1"></i> Plat:
                                <strong>{{ $peralatan->plat_nomor ?? '-' }}</strong>
                            </p>
                        </div>

                        {{-- Tanggal --}}
                        <div class="col-md-3 col-6">
                            <div class="mb-1">
                                <small class="text-muted d-block">Tgl Pengambilan:</small>
                                <strong class="small text-dark">
                                    <i class="bi bi-calendar-event me-1"></i>{{ $pickup->format('d M Y') }}
                                </strong>
                            </div>
                            <div>
                                <small class="text-muted d-block">Tgl Pengembalian:</small>
                                <strong class="small text-dark">
                                    <i class="bi bi-calendar-check me-1"></i>{{ $return->format('d M Y') }}
                                </strong>
                            </div>
                        </div>

                        {{-- Total Biaya --}}
                        <div class="col-md-2 col-6">
                            <small class="text-muted d-block">Total Biaya:</small>
                            <h6 class="fw-bold text-primary mb-0">
                                Rp {{ number_format($item->total_price, 0, ',', '.') }}
                            </h6>
                            <small class="text-muted font-monospace">({{ $durasi }} Hari)</small>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="col-md-2 col-6 text-end d-flex flex-column gap-1">
                            @if ($item->status === 'padding')
                                {{-- Batalkan --}}
                                <button type="button" class="btn btn-outline-danger btn-sm"
                                        onclick="confirmCancel({{ $item->id }}, '{{ $item->kode_penyewaan }}')">
                                    <i class="bi bi-x-circle me-1"></i> Batalkan
                                </button>
                                {{-- Upload Payment --}}
                                <a href="{{ route('penyewaan.payment.form', $item->id) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-upload me-1"></i> Upload Bayar
                                </a>

                            @elseif ($item->status === 'approved')
                                @if ($item->payment_status === 'unpaid')
                                    <a href="{{ route('penyewaan.payment.form', $item->id) }}" class="btn btn-primary btn-sm">
                                        <i class="bi bi-cash-coin me-1"></i> Bayar
                                    </a>
                                @else
                                    <span class="text-muted small"><i class="bi bi-lock me-1"></i>Menunggu Petugas</span>
                                @endif

                            @elseif ($item->status === 'rented')
                                <a href="{{ route('pengembalian.form', $item->id) }}" class="btn btn-success btn-sm">
                                    <i class="bi bi-arrow-return-left me-1"></i> Kembalikan
                                </a>

                            @elseif ($item->status === 'checking')
                                <span class="text-muted small"><i class="bi bi-search me-1"></i>Verifikasi Petugas</span>

                            @elseif ($item->status === 'denda')
                                <a href="{{ route('denda.bayar', $item->id) }}" class="btn btn-danger btn-sm">
                                    <i class="bi bi-exclamation-triangle me-1"></i> Bayar Denda
                                </a>

                            @else
                                <span class="text-muted small"><i class="bi bi-lock me-1"></i>{{ $st['label'] }}</span>
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

{{-- Modal Konfirmasi Cancel --}}
<div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="cancelModalLabel">
                    <i class="bi bi-exclamation-triangle text-warning me-2"></i>Konfirmasi Pembatalan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin membatalkan booking:</p>
                <div class="alert alert-warning">
                    <strong id="cancelBookingCode" class="font-monospace"></strong>
                </div>
                <p class="text-muted small mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    Status kendaraan akan kembali tersedia dan booking ini akan ditandai sebagai "Dibatalkan".
                </p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </button>
                <form id="cancelForm" method="POST" action="">
                    @csrf
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x-circle me-1"></i> Ya, Batalkan
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    function confirmCancel(id, kodeBooking) {
        document.getElementById('cancelBookingCode').textContent = kodeBooking;
        document.getElementById('cancelForm').action = `/booking/${id}/cancel`;
        const cancelModal = new bootstrap.Modal(document.getElementById('cancelModal'));
        cancelModal.show();
    }
</script>
@endsection
