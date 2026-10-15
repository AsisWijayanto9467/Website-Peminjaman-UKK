@extends('layouts.admin')

@section('title', 'Detail Booking - Petugas')

@section('style')
<style>
    .detail-card {
        border: none;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        border-radius: 12px;
    }
    .doc-preview {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
    }
    .step-indicator {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 12px;
    }
    .step-dot {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 700;
        background: #e9ecef;
        color: #6c757d;
    }
    .step-dot.active { background: #0d6efd; color: #fff; }
    .step-dot.done   { background: #198754; color: #fff; }
    .step-dot.danger { background: #dc3545; color: #fff; }
    .step-line {
        flex: 1;
        height: 2px;
        background: #e9ecef;
    }
    .step-line.done { background: #198754; }
</style>
@endsection

@section('main')
@php
    use Carbon\Carbon;
    $pickup = Carbon::parse($penyewaan->pickup_date);
    $return = Carbon::parse($penyewaan->return_date);
    $durasi = $pickup->diffInDays($return) ?: 1;

    $statusMap = [
        'padding'   => ['label' => 'Menunggu Konfirmasi', 'class' => 'bg-warning text-dark'],
        'approved'  => ['label' => 'Disetujui',           'class' => 'bg-info text-dark'],
        'rented'    => ['label' => 'Sedang Disewa',       'class' => 'bg-primary'],
        'checking'  => ['label' => 'Verifikasi',          'class' => 'bg-secondary'],
        'denda'     => ['label' => 'Ada Denda',           'class' => 'bg-danger'],
        'completed' => ['label' => 'Selesai',             'class' => 'bg-success'],
        'cancelled' => ['label' => 'Dibatalkan',          'class' => 'bg-danger'],
        'declined'  => ['label' => 'Ditolak',             'class' => 'bg-danger'],
    ];
    $st = $statusMap[$penyewaan->status] ?? ['label' => $penyewaan->status, 'class' => 'bg-secondary'];

    // Progress step (1=approval, 2=payment, 3=on-rent)
    $step = 1;
    if (in_array($penyewaan->status, ['padding'])) $step = 1;
    elseif ($penyewaan->status === 'approved' && $penyewaan->payment_status === 'unpaid') $step = 2;
    elseif ($penyewaan->status === 'approved' && $penyewaan->payment_status === 'paid')   $step = 3;
    elseif (in_array($penyewaan->status, ['rented','checking','denda','completed']))      $step = 4;

    $isRejected = in_array($penyewaan->status, ['declined', 'cancelled']);
@endphp

<div class="container-fluid py-2">

    {{-- ================= Header ================= --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item">
                        <a href="{{ route('petugas.penyewaan.list') }}">Booking</a>
                    </li>
                    <li class="breadcrumb-item active">#{{ $penyewaan->kode_penyewaan }}</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0">Detail Booking</h4>
        </div>
        <a href="{{ route('petugas.penyewaan.list') }}" class="btn btn-outline-secondary rounded-3">
            <i class="bi bi-arrow-left me-2"></i>Kembali
        </a>
    </div>

    {{-- Flash --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-1"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle me-1"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        {{-- ================= MAIN ================= --}}
        <div class="col-lg-8">

            {{-- Info Booking --}}
            <div class="card detail-card mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-info-circle me-2 text-primary"></i>Informasi Booking
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="text-muted" style="width: 130px;">Kode Booking</td>
                                    <td class="fw-bold text-primary">{{ $penyewaan->kode_penyewaan }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Tanggal Pesan</td>
                                    <td>{{ optional($penyewaan->created_at)->format('d M Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Durasi Sewa</td>
                                    <td><span class="fw-semibold">{{ $durasi }} Hari</span></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="text-muted" style="width: 130px;">Tanggal Sewa</td>
                                    <td>
                                        <i class="bi bi-calendar-check text-success me-1"></i>
                                        {{ $pickup->format('d M Y') }}
                                        <span class="mx-1">-</span>
                                        <i class="bi bi-calendar-x text-danger me-1"></i>
                                        {{ $return->format('d M Y') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Total Harga</td>
                                    <td class="fw-bold fs-5 text-success">
                                        Rp {{ number_format($penyewaan->total_price, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Data Diri Penyewa --}}
            <div class="card detail-card mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-person-badge me-2 text-primary"></i>Data Diri Penyewa
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 bg-light">
                                <small class="text-muted d-block mb-1">Nama Lengkap</small>
                                <strong>{{ $penyewaan->customer_name }}</strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 bg-light">
                                <small class="text-muted d-block mb-1">No. KTP / NIK</small>
                                <strong>{{ $penyewaan->customer_id_card }}</strong>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="border rounded-3 p-3 bg-light">
                                <small class="text-muted d-block mb-1">Alamat Lengkap</small>
                                <strong>{{ $penyewaan->customer_address }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Dokumen Verifikasi --}}
            <div class="card detail-card mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-camera me-2 text-primary"></i>Dokumen Verifikasi
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        {{-- Foto KTP --}}
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3">
                                <h6 class="fw-bold mb-3">
                                    <i class="bi bi-person-vcard me-2 text-primary"></i>Foto KTP
                                </h6>
                                <div class="doc-preview">
                                    @if ($penyewaan->id_card_photo)
                                        <img src="{{ asset('uploads/penyewaan/ktp/' . $penyewaan->id_card_photo) }}"
                                             alt="Foto KTP"
                                             class="img-fluid rounded-3 mb-2"
                                             style="max-height: 180px; object-fit: cover;">
                                        <a href="{{ asset('uploads/penyewaan/ktp/' . $penyewaan->id_card_photo) }}"
                                           target="_blank"
                                           class="btn btn-sm btn-primary rounded-3">
                                            <i class="bi bi-zoom-in me-1"></i>Lihat Penuh
                                        </a>
                                    @else
                                        <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                                        <p class="mt-2 mb-0 text-muted small fst-italic">
                                            Belum ada foto KTP
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Foto Wajah --}}
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3">
                                <h6 class="fw-bold mb-3">
                                    <i class="bi bi-person-circle me-2 text-primary"></i>Foto Wajah
                                </h6>
                                <div class="doc-preview">
                                    @if ($penyewaan->face_photo)
                                        <img src="{{ asset('uploads/penyewaan/face/' . $penyewaan->face_photo) }}"
                                             alt="Foto Wajah"
                                             class="img-fluid rounded-3 mb-2"
                                             style="max-height: 180px; object-fit: cover;">
                                        <a href="{{ asset('uploads/penyewaan/face/' . $penyewaan->face_photo) }}"
                                           target="_blank"
                                           class="btn btn-sm btn-primary rounded-3">
                                            <i class="bi bi-zoom-in me-1"></i>Lihat Penuh
                                        </a>
                                    @else
                                        <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                                        <p class="mt-2 mb-0 text-muted small fst-italic">
                                            Belum ada foto wajah
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bukti Pembayaran --}}
            <div class="card detail-card mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-receipt me-2 text-primary"></i>Bukti Pembayaran
                    </h5>
                </div>
                <div class="card-body">
                    <div class="doc-preview">
                        @if ($penyewaan->payment_photo)
                            <img src="{{ asset('uploads/penyewaan/payment/' . $penyewaan->payment_photo) }}"
                                 alt="Bukti Pembayaran"
                                 class="img-fluid rounded-3 mb-2"
                                 style="max-height: 320px; object-fit: contain;">
                            <div>
                                <a href="{{ asset('uploads/penyewaan/payment/' . $penyewaan->payment_photo) }}"
                                   target="_blank"
                                   class="btn btn-sm btn-primary rounded-3">
                                    <i class="bi bi-zoom-in me-1"></i>Lihat Penuh
                                </a>
                            </div>
                        @else
                            <i class="bi bi-receipt-cutoff text-muted" style="font-size: 3rem;"></i>
                            <p class="mt-2 mb-0 text-muted small fst-italic">
                                Belum ada foto pembayaran
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Detail Peralatan --}}
            <div class="card detail-card mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-box-seam me-2 text-primary"></i>Peralatan Disewa
                    </h5>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Peralatan</th>
                                <th>Kode</th>
                                <th>Harga/Hari</th>
                                <th>Jumlah</th>
                                <th>Durasi</th>
                                <th class="pe-4">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($penyewaan->detailPenyewaans as $d)
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="bg-primary bg-opacity-10 text-primary rounded d-flex align-items-center justify-content-center"
                                                 style="width: 50px; height: 35px;">
                                                <i class="bi bi-box-seam-fill"></i>
                                            </div>
                                            <span>{{ $d->peralatan->nama_barang ?? '-' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark font-monospace">
                                            {{ $d->peralatan->kode_barang ?? '-' }}
                                        </span>
                                    </td>
                                    <td>Rp {{ number_format($d->price_per_day, 0, ',', '.') }}</td>
                                    <td>{{ $d->jumlah }} unit</td>
                                    <td>{{ $d->durasi_hari }} hari</td>
                                    <td class="fw-semibold pe-4">
                                        Rp {{ number_format($d->subtotal, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        Tidak ada detail peralatan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="5" class="text-end">Total</th>
                                <th class="pe-4 text-success">
                                    Rp {{ number_format($penyewaan->total_price, 0, ',', '.') }}
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        {{-- ================= SIDEBAR ================= --}}
        <div class="col-lg-4">

            {{-- Akun Pelanggan --}}
            <div class="card detail-card mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-person me-2 text-primary"></i>Akun Pelanggan
                    </h5>
                </div>
                <div class="card-body text-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center mb-3"
                         style="width: 70px; height: 70px; font-size: 1.5rem; font-weight: 700;">
                        {{ strtoupper(substr($penyewaan->customer_name, 0, 1)) }}
                    </div>
                    <h6 class="fw-bold mb-1">{{ $penyewaan->customer_name }}</h6>
                    <small class="text-muted d-block">{{ $penyewaan->user->email ?? '-' }}</small>
                </div>
            </div>

            {{-- Panel Aksi Bertahap --}}
            <div class="card detail-card">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-gear me-2 text-primary"></i>Panel Aksi
                    </h5>
                </div>
                <div class="card-body">

                    {{-- Ringkasan Status --}}
                    <div class="border rounded-3 p-3 mb-3 bg-light">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small class="text-muted">Status Booking</small>
                            <span class="badge {{ $st['class'] }} rounded-pill">{{ $st['label'] }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">Pembayaran</small>
                            @if ($penyewaan->payment_status === 'paid')
                                <span class="badge bg-success rounded-pill">LUNAS</span>
                            @else
                                <span class="badge bg-secondary rounded-pill">BELUM BAYAR</span>
                            @endif
                        </div>
                    </div>

                    {{-- Step Indicator --}}
                    @if (!$isRejected)
                        <div class="step-indicator">
                            <div class="step-dot {{ $step > 1 ? 'done' : ($step == 1 ? 'active' : '') }}">1</div>
                            <div class="step-line {{ $step > 1 ? 'done' : '' }}"></div>
                            <div class="step-dot {{ $step > 2 ? 'done' : ($step == 2 ? 'active' : '') }}">2</div>
                            <div class="step-line {{ $step > 2 ? 'done' : '' }}"></div>
                            <div class="step-dot {{ $step > 3 ? 'done' : ($step == 3 ? 'active' : '') }}">3</div>
                        </div>
                    @endif

                    {{-- ============ TAHAP 1: Approve / Decline ============ --}}
                    @if ($penyewaan->status === 'padding')
                        <div class="alert alert-warning rounded-3 small mb-3">
                            <strong><i class="bi bi-hourglass-split me-1"></i> Menunggu Keputusan</strong>
                            <p class="mb-0 mt-1">Setujui atau tolak pengajuan booking ini.</p>
                        </div>

                        <form action="{{ route('petugas.penyewaan.approve', $penyewaan->id) }}"
                              method="POST" class="mb-2"
                              onsubmit="return confirm('Setujui booking {{ $penyewaan->kode_penyewaan }}?');">
                            @csrf
                            <button type="submit" class="btn btn-success w-100 rounded-3">
                                <i class="bi bi-check-circle me-2"></i>APPROVE
                            </button>
                        </form>

                        <form action="{{ route('petugas.penyewaan.decline', $penyewaan->id) }}"
                              method="POST"
                              onsubmit="return confirm('Tolak booking {{ $penyewaan->kode_penyewaan }}? Stok akan dikembalikan.');">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger w-100 rounded-3">
                                <i class="bi bi-x-circle me-2"></i>DECLINE
                            </button>
                        </form>
                    @endif

                    {{-- ============ TAHAP 2: Konfirmasi Pembayaran ============ --}}
                    @if ($penyewaan->status === 'approved' && $penyewaan->payment_status === 'unpaid')
                        <div class="alert alert-info rounded-3 small mb-3">
                            <strong><i class="bi bi-credit-card me-1"></i> Konfirmasi Pembayaran</strong>
                            <p class="mb-0 mt-1">
                                Periksa bukti pembayaran di halaman utama. Jika sudah sesuai, klik konfirmasi.
                            </p>
                        </div>

                        @if ($penyewaan->payment_photo)
                            <div class="alert alert-success small rounded-3 mb-3">
                                <i class="bi bi-check-circle me-1"></i>
                                Bukti pembayaran sudah diunggah pengguna.
                            </div>
                            <form action="{{ route('petugas.penyewaan.confirmPayment', $penyewaan->id) }}"
                                  method="POST"
                                  onsubmit="return confirm('Konfirmasi pembayaran booking {{ $penyewaan->kode_penyewaan }}?');">
                                @csrf
                                <button type="submit" class="btn btn-primary w-100 rounded-3">
                                    <i class="bi bi-cash-coin me-2"></i>KONFIRMASI PEMBAYARAN
                                </button>
                            </form>
                        @else
                            <div class="alert alert-secondary small rounded-3 mb-0">
                                <i class="bi bi-hourglass-split me-1"></i>
                                Belum ada foto pembayaran dari pengguna.
                            </div>
                        @endif
                    @endif

                    {{-- ============ TAHAP 3: Kendaraan Diambil (On Rent) ============ --}}
                    @if ($penyewaan->status === 'approved' && $penyewaan->payment_status === 'paid')
                        <div class="alert alert-info rounded-3 small mb-3">
                            <strong><i class="bi bi-truck me-1"></i> Serah Terima</strong>
                            <p class="mb-0 mt-1">
                                Klik jika kendaraan sudah diambil pelanggan.
                            </p>
                        </div>

                        <form action="{{ route('petugas.penyewaan.onRent', $penyewaan->id) }}"
                              method="POST"
                              onsubmit="return confirm('Tandai kendaraan sudah diambil untuk booking {{ $penyewaan->kode_penyewaan }}?');">
                            @csrf
                            <button type="submit" class="btn btn-primary w-100 rounded-3">
                                <i class="bi bi-truck me-2"></i>KENDARAAN DIAMBIL (ON RENT)
                            </button>
                        </form>
                    @endif

                    {{-- ============ STATUS SUDAH LEWAT TAHAP AWAL ============ --}}
                    @if (in_array($penyewaan->status, ['rented', 'checking', 'denda']))
                        <div class="alert alert-success rounded-3 small mb-0">
                            <i class="bi bi-info-circle me-1"></i>
                            Booking sedang berjalan.
                            <strong>{{ $st['label'] }}</strong>.
                            Aksi lanjutan (pengembalian/denda) di halaman terpisah.
                        </div>
                    @endif

                    @if ($penyewaan->status === 'completed')
                        <div class="alert alert-success rounded-3 small mb-0">
                            <i class="bi bi-check2-all me-1"></i>
                            Transaksi telah <strong>selesai</strong>.
                        </div>
                    @endif

                    @if ($isRejected)
                        <div class="alert alert-danger rounded-3 small mb-0">
                            <i class="bi bi-x-octagon me-1"></i>
                            Booking telah <strong>{{ $st['label'] }}</strong>.
                        </div>
                    @endif

                </div>
            </div>

        </div>
    </div>
</div>
@endsection
