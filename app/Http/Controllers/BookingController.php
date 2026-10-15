<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\DetailPenyewaan;
use App\Models\Keranjang;
use App\Models\Penyewaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $penyewaans = Penyewaan::with(["detailPenyewaans.peralatan", "pengembalians"])
            ->where("user_id", $user->id)
            ->orderBy("created_at", "desc")
            ->get();

        return view("UserRole.pengguna.History.index", compact("penyewaans"));
    }

    public function cancel(Request $request, string $id)
    {
        DB::beginTransaction();
        $user = Auth::user();

        try {
            // ================= Cari penyewaan milik user =================
            $penyewaan = Penyewaan::with("detailPenyewaans.peralatan")
                ->where("user_id", $user->id)
                ->where("id", $id)
                ->firstOrFail();

            // ================= Validasi status =================
            if ($penyewaan->status !== "padding") {
                throw new \Exception("Hanya penyewaan dengan status 'padding' yang bisa dibatalkan.");
            }

            // ================= Kembalikan stok peralatan =================
            foreach ($penyewaan->detailPenyewaans as $detail) {
                $peralatan = $detail->peralatan;

                if ($peralatan) {
                    $peralatan->update([
                        "stok_tersedia" => $peralatan->stok_tersedia + $detail->jumlah,
                    ]);
                }
            }

            // ================= Hapus detail penyewaan =================
            DetailPenyewaan::where("penyewaan_id", $penyewaan->id)->delete();

            // ================= Bersihkan sisa keranjang user (jaga-jaga) =================
            Keranjang::where("user_id", $user->id)->delete();

            // ================= Update status penyewaan =================
            $penyewaan->update([
                "status" => "cancelled",
            ]);

            // ================= Log aktivitas =================
            ActivityLog::create([
                "user_id"    => $user->id,
                "activity"   => $user->email . " berhasil membatalkan penyewaan dengan kode " . $penyewaan->kode_penyewaan,
                "status"     => "success",
                "ip_address" => $request->ip(),
                "user_agent" => $request->userAgent(),
            ]);

            DB::commit();

            return redirect()->back()->with("success", "Booking berhasil dibatalkan.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Terjadi kesalahan pada code di bagian ini: " . $e->getMessage());

            ActivityLog::create([
                "user_id"    => $user->id ?? null,
                "activity"   => ($user->email ?? 'Guest') . " gagal membatalkan penyewaan karena " . $e->getMessage(),
                "status"     => "failed",
                "ip_address" => $request->ip(),
                "user_agent" => $request->userAgent(),
            ]);

            return redirect()->back()->with("error", "Gagal membatalkan penyewaan karena error: " . $e->getMessage());
        }
    }
}
