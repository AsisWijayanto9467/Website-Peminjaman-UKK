<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Denda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PetugasDendaController extends Controller
{
    /**
     * List semua denda.
     */
    public function index(Request $request)
    {
        $query = Denda::with(["user", "penyewaan", "petugas"])
            ->orderBy("created_at", "desc");

        // Filter search
        if ($request->filled("search")) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where("kode_denda", "like", "%{$search}%")
                  ->orWhere("alasan_denda", "like", "%{$search}%")
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

        $dendas = $query->paginate(10)->withQueryString();

        // Statistik
        $stats = [
            "belum" => Denda::where("status", "belum dibayar")->count(),
            "sudah" => Denda::where("status", "sudah dibayar")->count(),
            "total" => Denda::sum("nominal"),
        ];

        return view("UserRole.petugas.denda.index", compact("dendas", "stats"));
    }

    /**
     * Detail denda.
     */
    public function detail(Request $request, string $id)
    {
        $denda = Denda::with([
                "user",
                "petugas",
                "penyewaan.detailPenyewaans.peralatan",
                "penyewaan.user",
                "pengembalian",
            ])
            ->findOrFail($id);

        return view("UserRole.petugas.denda.setting", compact("denda"));
    }

    /**
     * Konfirmasi pembayaran denda.
     * - status denda → sudah dibayar
     * - status penyewaan → completed
     * - stok peralatan dikembalikan
     */
    public function confirmPayment(Request $request, string $id)
    {
        DB::beginTransaction();
        $user = Auth::user();

        try {
            $denda = Denda::with([
                "penyewaan.detailPenyewaans.peralatan"
            ])->findOrFail($id);

            // Validasi
            if ($denda->status === "sudah dibayar") {
                throw new \Exception("Denda ini sudah dikonfirmasi sebelumnya.");
            }

            if (empty($denda->bukti_dibayar)) {
                throw new \Exception("Pengguna belum mengunggah bukti pembayaran denda.");
            }

            // ================= Update denda =================
            $denda->update([
                "status"     => "sudah dibayar",
                "petugas_id" => $user->id,
            ]);

            // ================= Update penyewaan → completed =================
            $penyewaan = $denda->penyewaan;

            if ($penyewaan) {
                $penyewaan->update([
                    "status"     => "completed",
                    "petugas_id" => $user->id,
                ]);

                // ================= Kembalikan stok peralatan =================
                foreach ($penyewaan->detailPenyewaans as $detail) {
                    if ($detail->peralatan) {
                        $stokBaru = $detail->peralatan->stok_tersedia + $detail->jumlah;

                        // Safety
                        if ($stokBaru > $detail->peralatan->stok_total) {
                            $stokBaru = $detail->peralatan->stok_total;
                        }

                        $detail->peralatan->update([
                            "stok_tersedia" => $stokBaru,
                        ]);
                    }
                }
            }

            // ================= Log =================
            ActivityLog::create([
                "user_id"    => $user->id,
                "activity"   => $user->email . " mengkonfirmasi pembayaran denda " . $denda->kode_denda
                                . " untuk booking " . ($penyewaan->kode_penyewaan ?? '-')
                                . " & mengembalikan stok",
                "status"     => "success",
                "ip_address" => $request->ip(),
                "user_agent" => $request->userAgent(),
            ]);

            DB::commit();

            return redirect()->route("petugas.denda.list")
                ->with("success", "Pembayaran denda dikonfirmasi. Booking selesai & stok peralatan telah dikembalikan.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Gagal konfirmasi denda: " . $e->getMessage());

            ActivityLog::create([
                "user_id"    => $user->id ?? null,
                "activity"   => ($user->email ?? 'Guest') . " gagal konfirmasi denda: " . $e->getMessage(),
                "status"     => "failed",
                "ip_address" => $request->ip(),
                "user_agent" => $request->userAgent(),
            ]);

            return redirect()->back()->with("error", "Gagal: " . $e->getMessage());
        }
    }
}
