<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Rental Mobil</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background-color: #f5f5f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px 0;
        }

        .register-card {
            max-width: 450px;
            width: 100%;
            border: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .register-header {
            background: linear-gradient(135deg, #198754, #157347);
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card register-card mx-auto">
                <div class="card-header register-header text-white text-center py-4">
                    <i class="bi bi-person-plus-fill" style="font-size: 2.5rem;"></i>
                    <h4 class="mt-2 mb-0">Register Rental Mobil</h4>
                    <small>Buat akun baru</small>
                </div>

                <div class="card-body p-4">
                    @if (session('error'))
                        <!-- Alert Error -->
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @if (session('success'))
                        <!-- Alert Success -->
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('register.procces') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" name="name" class="form-control"
                                       placeholder="Masukkan nama lengkap"  required>
                            </div>
                            <div class="mt-1">
                                <small class="text-muted">Minimal 3 karakter</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="email" class="form-control"
                                       placeholder="Masukkan email" required>
                            </div>
                            <div class="mt-1">
                                <small class="text-muted">Masukkan email yang valid</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nomor Telepon</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                <input type="tel" name="no_hp" class="form-control"
                                       placeholder="Masukkan nomor telepon" required>
                            </div>
                            <div class="mt-1">
                                <small class="text-muted">Contoh: 081234567890</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" name="password" class="form-control"
                                       placeholder="Masukkan password" required>
                            </div>
                            <div class="mt-1">
                                <small class="text-muted">Minimal 8 karakter</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Konfirmasi Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                <input type="password" name="password_confirmation" class="form-control"
                                       placeholder="Ulangi password" required>
                            </div>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="terms" checked>
                            <label class="form-check-label" for="terms">
                                Saya setuju dengan <a href="#" class="text-decoration-none">Syarat & Ketentuan</a>
                            </label>
                        </div>

                        <button type="submit" class="btn btn-success w-100 mb-3">
                            <i class="bi bi-person-plus me-1"></i> Daftar
                        </button>
                    </form>

                    <hr class="my-3">

                    <div class="text-center">
                        <small>Sudah punya akun?
                            <a href="{{ route('login') }}">Login sekarang</a>
                        </small>
                    </div>

                </div>

                <div class="card-footer text-center bg-light">
                    <a href="{{ route('pengguna.beranda') }}" class="text-decoration-none">
                        <i class="bi bi-arrow-left me-1"></i> Kembali ke Beranda
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
