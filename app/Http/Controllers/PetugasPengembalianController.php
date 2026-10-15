<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Pengembalian;
use App\Models\Penyewaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PetugasPengembalianController extends Controller
{
    /**
     * List semua pengajuan pengembalian.
     */
    public function index(Request $request)
    {
        $query = Pengembalian::with(["user", "penyewaan.detailPenyewaans.peralatan", "petugas"])
            ->orderBy("created_at", "desc");

        // Filter search
        if ($request->filled("search")) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where("catatan_pengguna", "like", "%{$search}%")
                  ->orWhereHas("penyewaan", function ($q2) use ($search) {
                      $q2->where("kode_penyewaan", "like", "%{$search}%")
                         ->orWhere("customer_name", "like", "%{$search}%");
                  });
            });
        }

        // Filter status
        if ($request->filled("status")) {
            $query->where("status", $request->status);
        }

        // Filter tanggal
        if ($request->filled("date_from")) {
            $query->whereDate("tanggal_pengajuan", ">=", $request->date_from);
        }
        if ($request->filled("date_to")) {
            $query->whereDate("tanggal_pengajuan", "<=", $request->date_to);
        }

        $pengembalians = $query->paginate(10)->withQueryString();

        // Statistik
        $stats = [
            "padding" => Pengembalian::where("status", "padding")->count(),
            "accept"  => Pengembalian::where("status", "accept")->count(),
            "reject"  => Pengembalian::where("status", "reject")->count(),
        ];

        return view("UserRole.petugas.pengembalian.index", compact("pengembalians", "stats"));
    }

    /**
     * Detail pengembalian (setting / kelola).
     */
    public function detail(Request $request, string $id)
    {
        $pengembalian = Pengembalian::with([
                "user",
                "petugas",
                "penyewaan.detailPenyewaans.peralatan",
                "penyewaan.user",
            ])
            ->findOrFail($id);

        return view("UserRole.petugas.pengembalian.setting", compact("pengembalian"));
    }

    /**
     * ACCEPT pengembalian.
     * - status pengembalian → accept
     * - status penyewaan → completed
     * - STOK peralatan dikembalikan ke stok_tersedia
     */
    public function accept(Request $request, string $id)
    {
        DB::beginTransaction();
        $user = Auth::user();

        try {
            $pengembalian = Pengembalian::with([
                "penyewaan.detailPenyewaans.peralatan"
            ])->findOrFail($id);

            if ($pengembalian->status !== "padding") {
                throw new \Exception("Pengembalian ini sudah diproses sebelumnya.");
            }

            // ================= Update pengembalian =================
            $pengembalian->update([
                "status"             => "accept",
                "petugas_id"         => $user->id,
                "catatan_petugas"    => $request->catatan_petugas,
                "tanggal_konfirmasi" => now()->toDateString(),
            ]);

            // ================= Update penyewaan → completed =================
            $penyewaan = $pengembalian->penyewaan;

            if ($penyewaan) {
                $penyewaan->update([
                    "status"     => "completed",
                    "petugas_id" => $user->id,
                ]);

                // ================= KEMBALIKAN STOK PERALATAN =================
                foreach ($penyewaan->detailPenyewaans as $detail) {
                    $peralatan = $detail->peralatan;

                    if ($peralatan) {
                        $stokBaru = $peralatan->stok_tersedia + $detail->jumlah;

                        // Batasi agar tidak melebihi stok_total (opsional, safety)
                        if ($stokBaru > $peralatan->stok_total) {
                            $stokBaru = $peralatan->stok_total;
                        }

                        $peralatan->update([
                            "stok_tersedia" => $stokBaru,
                        ]);
                    }
                }
            }

            // ================= Log =================
            ActivityLog::create([
                "user_id"    => $user->id,
                "activity"   => $user->email . " menyetujui pengembalian booking " . ($penyewaan->kode_penyewaan ?? '-') . " & mengembalikan stok",
                "status"     => "success",
                "ip_address" => $request->ip(),
                "user_agent" => $request->userAgent(),
            ]);

            DB::commit();

            return redirect()->route("petugas.pengembalian.list")
                ->with("success", "Pengembalian disetujui. Status booking menjadi Selesai & stok peralatan telah dikembalikan.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Gagal accept pengembalian: " . $e->getMessage());

            ActivityLog::create([
                "user_id"    => $user->id ?? null,
                "activity"   => ($user->email ?? 'Guest') . " gagal menyetujui pengembalian: " . $e->getMessage(),
                "status"     => "failed",
                "ip_address" => $request->ip(),
                "user_agent" => $request->userAgent(),
            ]);

            return redirect()->back()->with("error", "Gagal: " . $e->getMessage());
        }
    }

    /**
     * REJECT pengembalian.
     * - status pengembalian → reject
     * - status penyewaan → denda
     * - STOK TIDAK dikembalikan (karena masih dalam proses denda)
     */
    public function reject(Request $request, string $id)
    {
        DB::beginTransaction();
        $user = Auth::user();

        try {
            $request->validate([
                "catatan_petugas" => "required|string|max:1000",
            ], [
                "catatan_petugas.required" => "Catatan wajib diisi saat menolak pengembalian.",
            ]);

            $pengembalian = Pengembalian::with("penyewaan")->findOrFail($id);

            if ($pengembalian->status !== "padding") {
                throw new \Exception("Pengembalian ini sudah diproses sebelumnya.");
            }

            // Update pengembalian
            $pengembalian->update([
                "status"             => "reject",
                "petugas_id"         => $user->id,
                "catatan_petugas"    => $request->catatan_petugas,
                "tanggal_konfirmasi" => now()->toDateString(),
            ]);

            // Update penyewaan → denda
            if ($pengembalian->penyewaan) {
                $pengembalian->penyewaan->update([
                    "status"     => "denda",
                    "petugas_id" => $user->id,
                ]);
            }

            // Log
            ActivityLog::create([
                "user_id"    => $user->id,
                "activity"   => $user->email . " menolak pengembalian booking " . ($pengembalian->penyewaan->kode_penyewaan ?? '-'),
                "status"     => "success",
                "ip_address" => $request->ip(),
                "user_agent" => $request->userAgent(),
            ]);

            DB::commit();

            return redirect()->route("petugas.pengembalian.list")
                ->with("success", "Pengembalian ditolak. Status booking menjadi Ada Denda.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Gagal reject pengembalian: " . $e->getMessage());

            ActivityLog::create([
                "user_id"    => $user->id ?? null,
                "activity"   => ($user->email ?? 'Guest') . " gagal menolak pengembalian: " . $e->getMessage(),
                "status"     => "failed",
                "ip_address" => $request->ip(),
                "user_agent" => $request->userAgent(),
            ]);

            return redirect()->back()->with("error", "Gagal: " . $e->getMessage());
        }
    }

    public function dendaForm(Request $request, string $id)
    {
        $pengembalian = Pengembalian::with([
                "penyewaan.detailPenyewaans.peralatan",
                "penyewaan.user",
                "user",
            ])
            ->findOrFail($id);

        // Validasi: hanya dari pengembalian yang reject
        if ($pengembalian->status !== "reject") {
            return redirect()->route("petugas.pengembalian.list")
                ->with("error", "Denda hanya bisa dibuat dari pengembalian yang ditolak.");
        }

        // Cegah duplikat denda
        $existing = \App\Models\Denda::where("pengembalian_id", $pengembalian->id)->exists();
        if ($existing) {
            return redirect()->route("petugas.pengembalian.setting", $pengembalian->id)
                ->with("error", "Denda untuk pengembalian ini sudah pernah dibuat.");
        }

        // Hitung estimasi denda keterlambatan
        $penyewaan = $pengembalian->penyewaan;
        $estimasiDenda = 0;
        $dendaHari = 0;

        if ($penyewaan) {
            $returnDate = \Carbon\Carbon::parse($penyewaan->return_date);
            $actualDate = \Carbon\Carbon::parse($pengembalian->actual_return_date);

            if ($actualDate->greaterThan($returnDate)) {
                $dendaHari = $returnDate->diffInDays($actualDate);
                foreach ($penyewaan->detailPenyewaans as $d) {
                    $dendaPerHari = $d->peralatan->price_denda_perhari ?? 0;
                    $estimasiDenda += $dendaPerHari * $d->jumlah * $dendaHari;
                }
            }
        }

        return view("UserRole.petugas.pengembalian.dendaForm", compact("pengembalian", "estimasiDenda", "dendaHari"));
    }

    /**
     * Simpan denda.
     */
    public function dendaStore(Request $request, string $id)
    {
        DB::beginTransaction();
        $user = Auth::user();

        try {
            $request->validate([
                "nominal"      => "required|numeric|min:0",
                "alasan_denda" => "required|string|max:1000",
            ], [
                "nominal.required"      => "Nominal denda wajib diisi.",
                "nominal.numeric"       => "Nominal harus berupa angka.",
                "nominal.min"           => "Nominal tidak boleh negatif.",
                "alasan_denda.required" => "Alasan denda wajib diisi.",
                "alasan_denda.max"      => "Alasan denda maksimal 1000 karakter.",
            ]);

            $pengembalian = Pengembalian::with("penyewaan")->findOrFail($id);

            if ($pengembalian->status !== "reject") {
                throw new \Exception("Denda hanya bisa dibuat dari pengembalian yang ditolak.");
            }

            $existing = \App\Models\Denda::where("pengembalian_id", $pengembalian->id)->exists();
            if ($existing) {
                throw new \Exception("Denda untuk pengembalian ini sudah ada.");
            }

            // Generate kode denda
            $kodeDenda = "DND-" . date("Ymd") . "-" . str_pad(
                (\App\Models\Denda::whereDate("created_at", today())->count() + 1),
                3, "0", STR_PAD_LEFT
            );

            \App\Models\Denda::create([
                "kode_denda"      => $kodeDenda,
                "pengembalian_id" => $pengembalian->id,
                "penyewaan_id"    => $pengembalian->penyewaan_id,
                "user_id"         => $pengembalian->user_id,
                "petugas_id"      => $user->id,
                "nominal"         => $request->nominal,
                "alasan_denda"    => $request->alasan_denda,
                "status"          => "belum dibayar",
                "bukti_dibayar"   => null,
            ]);

            ActivityLog::create([
                "user_id"    => $user->id,
                "activity"   => $user->email . " membuat denda " . $kodeDenda
                                . " untuk booking " . ($pengembalian->penyewaan->kode_penyewaan ?? '-')
                                . " nominal Rp " . number_format($request->nominal, 0, ',', '.'),
                "status"     => "success",
                "ip_address" => $request->ip(),
                "user_agent" => $request->userAgent(),
            ]);

            DB::commit();

            return redirect()->route("petugas.pengembalian.setting", $pengembalian->id)
                ->with("success", "Denda berhasil dibuat. Menunggu pembayaran dari pengguna.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Gagal membuat denda: " . $e->getMessage());

            ActivityLog::create([
                "user_id"    => $user->id ?? null,
                "activity"   => ($user->email ?? 'Guest') . " gagal membuat denda: " . $e->getMessage(),
                "status"     => "failed",
                "ip_address" => $request->ip(),
                "user_agent" => $request->userAgent(),
            ]);

            return redirect()->back()->withInput()
                ->with("error", "Gagal membuat denda: " . $e->getMessage());
        }
    }
}
