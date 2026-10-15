@extends('layouts.admin');

@section('title', 'dashboard')

@section('style')

@endsection

@section('main')
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold">
            <i class="bi bi-folder-plus me-2 text-primary"></i>Edit Kategori
        </h3>
        <a href="{{ route('kategori.list') }}" class="btn btn-outline-secondary rounded-3">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <!-- Form Card -->
    <div class="card form-card">
        <div class="card-body p-4">
            <form action="{{ route("kategori.update", $kategori->id) }}" method="POST">
                @csrf
                @method("PUT")
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">
                            Nama Kategori <span class="required-mark">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-tag text-muted"></i>
                            </span>
                            <input value="{{ old("nama_kategori", $kategori->nama_kategori) }}" type="text" name="nama_kategori" class="form-control border-start-0"
                                placeholder="Contoh: SUV, Sedan, MPV" required autofocus>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Status <span class="required-mark">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-person-badge text-muted"></i>
                            </span>
                            <select name="status" class="form-select border-start-0" required>
                                <option value="">-- Pilih Status --</option>
                                <option value="aktif" {{ old("kategori", $kategori->status == 'aktif' ? "selected" : "") }}>Aktif</option>
                                <option value="nonaktif" {{ old("kategori", $kategori->status == 'nonaktif' ? "selected" : "") }}>Non-Aktif</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label">
                            Deskripsi <small class="text-muted">(Opsional)</small>
                        </label>
                        <textarea name="deskripsi_kategori" class="form-control" rows="3"
                            placeholder="Deskripsi singkat tentang kategori kendaraan ini...">{{ old("deskripsi_kategori", $kategori->deskripsi_kategori) }}</textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('kategori.list') }}" class="btn btn-secondary rounded-3 px-4">
                        <i class="bi bi-x me-1"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-primary rounded-3 px-4">
                        <i class="bi bi-check-circle me-1"></i> Simpan Kategori
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('script')

@endsection
