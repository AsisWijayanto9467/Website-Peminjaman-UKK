<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rental Kendaraan - @yield('title', 'Beranda')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background-color: #f5f5f5;
            padding-top: 70px;
        }

        .navbar {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .card {
            border: none;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .status-badge {
            font-size: 0.85rem;
            padding: 5px 10px;
        }

        .vehicle-img {
            height: 200px;
            object-fit: cover;
            background-color: #e9ecef;
        }

        .user-dropdown {
            cursor: pointer;
        }

        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background-color: #0d6efd;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            overflow: hidden;
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .nav-link.active {
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 5px;
        }

        .profile-photo-label {
            transition: all 0.3s;
        }

        .profile-photo-label:hover {
            transform: scale(1.1);
        }
    </style>
    @yield('style')
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="bi bi-car-front-fill me-2"></i>
                <strong>Rental Mobil</strong>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('pengguna.beranda') ? 'active' : '' }}" href="{{ route("pengguna.beranda") }}">
                            <i class="bi bi-house-door me-1"></i> Beranda
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs("katalog.list") ? 'active' : '' }}" href="{{ route("katalog.list") }}">
                            <i class="bi bi-car-front me-1"></i> Katalog Kendaraan
                        </a>
                    </li>
                    @auth
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs("booking.list") ? 'active' : '' }}" href="{{ route("booking.list", Auth::user()->id) }}">
                                <i class="bi bi-journal-text me-1"></i> Booking Saya
                            </a>
                        </li>
                    @endauth
                </ul>

                <div class="d-flex align-items-center">
                    @auth
                        <a href="{{ route("keranjang.index") }}" class="btn btn-outline-light me-2">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Keranjang
                        </a>
                        <div class="dropdown">
                            <a class="nav-link dropdown-toggle text-white d-flex align-items-center user-dropdown"
                                href="#" role="button" data-bs-toggle="dropdown">
                                @php
                                    $user = Auth::user();
                                @endphp
                                <div class="user-avatar me-2 shadow-sm">
                                    @if ($user->photo_profile && file_exists(public_path("uploads/photo_profile/" . $user->photo_profile)))
                                        <img src="{{ asset("uploads/photo_profile/" . $user->photo_profile) }}" alt="Avatar">
                                    @elseif (file_exists(public_path("images/profile.jpg")))
                                        <img src="{{ asset("images/profile.jpg") }}" alt="Avatar" />
                                    @else
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    @endif
                                </div>
                                <div class="d-none d-md-block text-start">
                                    <div class="small fw-bold text-white lh-1">{{ $user->name }}</div>
                                    <div class="small text-white-50" style="font-size: 0.75rem;">{{ $user->email }}</div>
                                </div>
                            </a>

                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-3 mt-2">
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2" href="#">
                                        <i class="bi bi-person"></i> Profil Saya
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2" href="#">
                                        <i class="bi bi-journal-check"></i> Booking Saya
                                    </a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <form action="{{ route("logout.procces") }}" method="POST" onsubmit="return confirm('Anda yakin ingin logout?')">
                                        @csrf
                                        <button type="submit" class="dropdown-item d-flex align-items-center gap-2 text-danger">
                                            <i class="bi bi-box-arrow-right"></i> Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @else
                        <a href="{{ route("login") }}" class="btn btn-outline-light me-2">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Login
                        </a>
                        <a href="{{ route('register') }}" class="btn btn-light">
                            <i class="bi bi-person-plus me-1"></i> Register
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
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

        @yield('main')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @yield('script')
</body>

</html>
