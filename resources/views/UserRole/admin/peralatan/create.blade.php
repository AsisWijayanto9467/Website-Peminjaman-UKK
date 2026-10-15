@extends('layouts.admin');

@section('title', 'dashboard')

@section('style')

@endsection

@section('main')
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold">
            <i class="bi bi-car-front-fill me-2 text-primary"></i>Tambah Peralatan
        </h3>
        <a href="{{ route("peralatan.list") }}" class="btn btn-outline-secondary rounded-3">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <!-- Form Card -->
    <div class="card form-card">
        <div class="card-body p-4">
            <form action="{{ route("peralatan.store") }}" method="POST" enctype="multipart/form-data" id="addVehicleForm">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            Nama Barang <span class="required-mark">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-car-front text-muted"></i>
                            </span>
                            <input type="text" name="nama_barang" class="form-control border-start-0"
                                placeholder="Contoh: Toyota Avanza, Honda Civic" required autofocus>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            Kode Barang <span class="required-mark">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-hash text-muted"></i>
                            </span>
                            <input type="text" name="kode_barang" class="form-control border-start-0"
                                placeholder="Contoh: B 1234 ABC" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">
                            Kategori <span class="required-mark">*</span>
                        </label>
                        <select name="category_id" class="form-select" required>
                            <option value="">-- Pilih Kategori --</option>
                            @forelse ($category as $c)
                                <option value="{{ $c->id }}">{{ $c->nama_kategori }}</option>
                            @empty
                                <option value="">Tidak ada</option>
                            @endforelse
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">
                            Harga Sewa / Hari (Rp) <span class="required-mark">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">Rp</span>
                            <input type="number" name="price_per_day" class="form-control" placeholder="Contoh: 350000"
                                min="0" required>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">
                            Nominal Denda / Hari (Rp) <span class="required-mark">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">Rp</span>
                            <input type="number" name="price_denda_perhari" class="form-control" placeholder="Contoh: 350000"
                                min="0" required>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">
                            Stok <span class="required-mark">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">Rp</span>
                            <input type="number" name="stok_total" class="form-control" placeholder="Contoh: 20"
                                min="0" required>
                        </div>
                    </div>


                    <div class="col-md-4 mb-3">
                        <label class="form-label">Status <span class="required-mark">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-person-badge text-muted"></i>
                            </span>
                            <select name="status" class="form-select border-start-0" required>
                                <option value="">-- Pilih Status --</option>
                                <option value="aktif">Aktif</option>
                                <option value="nonAktif">Non-Aktif</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label">
                            Deskripsi <small class="text-muted">(Opsional)</small>
                        </label>
                        <textarea name="deskripsi" class="form-control" rows="3"
                            placeholder="Deskripsi singkat tentang kategori peralatan ini..."></textarea>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Foto Peralatan (Bisa pilih lebih dari satu)</label>
                        <input type="file" name="gambar_peralatan" class="form-control">
                        <div class="form-text mt-2">
                            <i class="bi bi-info-circle me-1"></i>
                            Format yang didukung: JPG, JPEG, PNG, WEBP. Maksimal 2MB per gambar.
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-3 pt-3 border-top">
                    <a href="{{ route("peralatan.list") }}" class="btn btn-secondary rounded-3 px-4">
                        <i class="bi bi-x me-1"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-primary rounded-3 px-4">
                        <i class="bi bi-check-circle me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('script')

@endsection
