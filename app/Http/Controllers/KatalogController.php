<?php
// app/Http/Controllers/KatalogController.php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Keranjang;
use App\Models\Peralatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KatalogController extends Controller
{
    /**
     * Menampilkan semua peralatan dengan filter search & kategori.
     */
    public function index(Request $request)
    {
        // Ambil semua kategori untuk dropdown filter
        $categories = Category::where('status', 'aktif')
            ->orderBy('nama_kategori')
            ->get();

        // Query peralatan + relasi kategori
        $query = Peralatan::with('category');

        // Filter pencarian (nama_barang / kode_barang)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_barang', 'like', '%' . $search . '%')
                  ->orWhere('kode_barang', 'like', '%' . $search . '%');
            });
        }

        // Filter kategori
        if ($request->filled('kategori')) {
            $query->where('category_id', $request->kategori);
        }

        // Urutkan: yang tersedia dulu, lalu nama
        $peralatans = $query->orderBy('nama_barang')->paginate(8);

        return view("UserRole.pengguna.katalog.index", compact('peralatans', 'categories'));
    }

    /**
     * Menampilkan detail satu peralatan.
     */
    public function show(string $id)
    {
        $peralatan = Peralatan::with('category')->findOrFail($id);

        return view("UserRole.pengguna.katalog.detail", compact('peralatan'));
    }

    public function addToCart(Request $request, $id)
    {
        DB::beginTransaction();
        $user = Auth::user();

        try {
            $request->validate([
                "jumlah" => "required|integer|min:1",
            ]);

            $peralatan = Peralatan::findOrFail($id);
            $jumlahTambahan = $request->jumlah;

            // Validasi apakah jumlah yang diminta melebihi stok tersedia saat ini
            if ($jumlahTambahan > $peralatan->stok_tersedia) {
                return redirect()->back()->with("error", "Jumlah melebihi stok yang tersedia.");
            }

            // Cek apakah item sudah ada di keranjang user
            $keranjang = Keranjang::where('user_id', $user->id)
                ->where('peralatan_id', $peralatan->id)
                ->first();

            if ($keranjang) {
                $jumlahBaru = $keranjang->jumlah + $jumlahTambahan;
                $keranjang->update(['jumlah' => $jumlahBaru]);
            } else {
                Keranjang::create([
                    "user_id" => $user->id,
                    "peralatan_id" => $peralatan->id,
                    "jumlah" => $jumlahTambahan,
                ]);
            }

            // Kurangi stok tersedia pada tabel peralatans
            $peralatan->update([
                'stok_tersedia' => $peralatan->stok_tersedia - $jumlahTambahan
            ]);

            ActivityLog::create([
                "user_id" => $user->id,
                "activity" => $user->email . " berhasil menambahkan " . $peralatan->nama_barang . " ke keranjang",
                "status" => "success",
                "ip_address" => $request->ip(),
                "user_agent" => $request->userAgent()
            ]);

            DB::commit();

            return redirect()->route("katalog.detail", $id)->with("success", "Peralatan berhasil ditambahkan ke keranjang.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Terjadi kesalahan pada code di bagian ini " . $e->getMessage());

            ActivityLog::create([
                "user_id" => $user->id ?? null,
                "activity" => ($user->email ?? 'Guest') . " gagal menambahkan item ke keranjang karena " . $e->getMessage(),
                "status" => "failed",
                "ip_address" => $request->ip(),
                "user_agent" => $request->userAgent()
            ]);

            return redirect()->back()->with("error", ($user->email ?? 'Guest') . " gagal menambahkan item ke keranjang karena error ini " . $e->getMessage());
        }
    }
}
