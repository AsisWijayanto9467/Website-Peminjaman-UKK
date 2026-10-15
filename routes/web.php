<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AdminPenyewaanController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BerandaController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KatalogController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PenyewaanController;
use App\Http\Controllers\PeralatanController;
use App\Http\Controllers\PetugasDendaController;
use App\Http\Controllers\PetugasPengembalianController;
use App\Http\Controllers\PetugasPenyewaanController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get("/", [BerandaController::class, "beranda"])->name("pengguna.beranda");
Route::get("/login", [AuthController::class, "showLogin"])->name("login");
Route::post("/login-pocces", [AuthController::class, "login"])->name("login.procces");
Route::get("/register", [AuthController::class, "showRegister"])->name("register");
Route::post("/register-pocces", [AuthController::class, "register"])->name("register.procces");

Route::middleware("auth")->group(function() {
    Route::post("/logout-pocces", [AuthController::class, "logout"])->name("logout.procces");
    Route::get("/dashboard", [DashboardController::class, "dashboard"])->name("admin.dashboard");

    Route::get("/katalog", [KatalogController::class, "index"])->name("katalog.list");
    Route::get("/katalog/{id}", [KatalogController::class, "show"])->name("katalog.detail");
    Route::post("/katalog/{id}/keranjang", [KatalogController::class, "addToCart"])->name("keranjang.store");

    Route::get("/keranjang", [PenyewaanController::class, "keranjang"])->name("keranjang.index");
    Route::patch("/keranjang/{id}/{action}", [PenyewaanController::class, "updateQty"])->name("keranjang.updateQty");
    Route::delete("/keranjang/{id}", [PenyewaanController::class, "destroy"])->name("keranjang.destroy");
    Route::get("/penyewaan/qr", [PenyewaanController::class, "qr"])->name("penyewaan.qr");
    Route::get("/penyewaan/booking", [PenyewaanController::class, "booking"])->name("penyewaan.booking");
    Route::post("/penyewaan/store", [PenyewaanController::class, "store"])->name("penyewaan.store");

    Route::get("/booking/user/{id}", [BookingController::class, "index"])->name("booking.list");
    Route::post('/booking/{id}/cancel', [BookingController::class, 'cancel'])->name('penyewaan.cancel');

    Route::middleware("admin")->group(function() {
        Route::get("/activity-log", [ActivityController::class, "index"])->name("activity.list");

        Route::get("/kategori", [KategoriController::class, "index"])->name("kategori.list");
        Route::get("/kategori/create", [KategoriController::class, "create"])->name("kategori.create");
        Route::post("/kategori/store", [KategoriController::class, "store"])->name("kategori.store");
        Route::get("/kategori/{id}", [KategoriController::class, "edit"])->name("kategori.edit");
        Route::put("/kategori/update/{id}", [KategoriController::class, "update"])->name("kategori.update");
        Route::delete("/kategori/destroy/{id}", [KategoriController::class, "destroy"])->name("kategori.destroy");

        Route::get("/user", [UserController::class, "index"])->name("user.list");
        Route::get("/user/create", [UserController::class, "create"])->name("user.create");
        Route::post("/user/store", [UserController::class, "store"])->name("user.store");
        Route::get("/user/{id}", [UserController::class, "edit"])->name("user.edit");
        Route::put("/user/update/{id}", [UserController::class, "update"])->name("user.update");
        Route::delete("/user/destroy/{id}", [UserController::class, "destroy"])->name("user.destroy");

        Route::get("/peralatan", [PeralatanController::class, "index"])->name("peralatan.list");
        Route::get("/peralatan/create", [PeralatanController::class, "create"])->name("peralatan.create");
        Route::post("/peralatan/store", [PeralatanController::class, "store"])->name("peralatan.store");
        Route::get("/peralatan/{id}", [PeralatanController::class, "edit"])->name("peralatan.edit");
        Route::put("/peralatan/update/{id}", [PeralatanController::class, "update"])->name("peralatan.update");
        Route::delete("/peralatan/destroy/{id}", [PeralatanController::class, "destroy"])->name("peralatan.destroy");

        Route::get("/admin-penyewaan", [AdminPenyewaanController::class, "index"])->name("adminPenyewaan.list");
    });

    Route::middleware("petugas")->group(function() {
        Route::get("/penyewaan/admin", [PetugasPenyewaanController::class, "index"])->name("petugas.penyewaan.list");
        Route::get("/penyewaan/admin/{id}", [PetugasPenyewaanController::class, "detail"])->name("petugas.penyewaan.setting");

        Route::get("/pengembalian/admin", [PetugasPengembalianController::class, "index"])->name("petugas.pengembalian.list");
        Route::get("/pengembalian/admin/{id}", [PetugasPengembalianController::class, "detail"])->name("petugas.pengembalian.setting");

        Route::get("/denda/admin", [PetugasDendaController::class, "index"])->name("petugas.denda.list");
        Route::get("/denda/admin/{id}", [PetugasDendaController::class, "detail"])->name("petugas.denda.setting");

        Route::get("/report/admin/", [LaporanController::class, "index"])->name("petugas.report.list");
    });
});
