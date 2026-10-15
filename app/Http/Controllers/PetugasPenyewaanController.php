<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Penyewaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PetugasPenyewaanController extends Controller
{
    /**
     * Tampilkan daftar semua penyewaan.
     */
    public function index(Request $request)
    {
        $query = Penyewaan::with(["user", "detailPenyewaans.peralatan", "petugas"])
            ->orderBy("created_at", "desc");

        if ($request->filled("search")) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where("kode_penyewaan", "like", "%{$search}%")
                  ->orWhere("customer_name", "like", "%{$search}%")
                  ->orWhere("customer_id_card", "like", "%{$search}%");
            });
        }

        if ($request->filled("status")) {
            $query->where("status", $request->status);
        }

        if ($request->filled("date_from")) {
            $query->whereDate("pickup_date", ">=", $request->date_from);
        }
        if ($request->filled("date_to")) {
            $query->whereDate("return_date", "<=", $request->date_to);
        }

        $penyewaans = $query->paginate(10)->withQueryString();

        $stats = [
            "padding"   => Penyewaan::where("status", "padding")->count(),
            "approved"  => Penyewaan::where("status", "approved")->count(),
            "rented"    => Penyewaan::where("status", "rented")->count(),
            "completed" => Penyewaan::where("status", "completed")->count(),
            "cancelled" => Penyewaan::where("status", "cancelled")->count(),
        ];

        return view("UserRole.petugas.penyewaan.index", compact("penyewaans", "stats"));
    }

    /**
     * Detail penyewaan.
     */
    public function detail(Request $request, string $id)
    {
        $penyewaan = Penyewaan::with([
                "user",
                "petugas",
                "detailPenyewaans.peralatan",
                "pengembalians",
            ])
            ->findOrFail($id);

        return view("UserRole.petugas.penyewaan.setting", compact("penyewaan"));
    }

    /**
     * Aksi 1: Approve booking.
     * Status: padding → approved
     */
    public function approve(Request $request, string $id)
    {
        return $this->changeStatus($request, $id, "approved", "menyetujui booking");
    }

    /**
     * Aksi 1: Decline booking.
     * Status: padding → declined (+ kembalikan stok)
     */
    public function decline(Request $request, string $id)
    {
        DB::beginTransaction();
        $user = Auth::user();

        try {
            $penyewaan = Penyewaan::with("detailPenyewaans.peralatan")->findOrFail($id);

            if ($penyewaan->status !== "padding") {
                throw new \Exception("Hanya booking dengan status 'padding' yang bisa ditolak.");
            }

            // Kembalikan stok peralatan
            foreach ($penyewaan->detailPenyewaans as $detail) {
                if ($detail->peralatan) {
                    $detail->peralatan->update([
                        "stok_tersedia" => $detail->peralatan->stok_tersedia + $detail->jumlah,
                    ]);
                }
            }

            $penyewaan->update([
                "status"     => "declined",
                "petugas_id" => $user->id,
            ]);

            ActivityLog::create([
                "user_id"    => $user->id,
                "activity"   => $user->email . " menolak booking " . $penyewaan->kode_penyewaan,
                "status"     => "success",
                "ip_address" => $request->ip(),
                "user_agent" => $request->userAgent(),
            ]);

            DB::commit();
            return redirect()->back()->with("success", "Booking berhasil ditolak.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Gagal menolak booking: " . $e->getMessage());
            return redirect()->back()->with("error", "Gagal menolak booking: " . $e->getMessage());
        }
    }

    /**
     * Aksi 2: Konfirmasi pembayaran.
     * Status: approved (payment_status: unpaid → paid)
     */
    public function confirmPayment(Request $request, string $id)
    {
        DB::beginTransaction();
        $user = Auth::user();

        try {
            $penyewaan = Penyewaan::findOrFail($id);

            if ($penyewaan->status !== "approved") {
                throw new \Exception("Booking harus berstatus 'approved' sebelum konfirmasi pembayaran.");
            }

            if ($penyewaan->payment_status === "paid") {
                throw new \Exception("Pembayaran booking ini sudah dikonfirmasi sebelumnya.");
            }

            if (empty($penyewaan->payment_photo)) {
                throw new \Exception("Belum ada bukti pembayaran yang diunggah oleh pengguna.");
            }

            $penyewaan->update([
                "payment_status" => "paid",
                "petugas_id"     => $user->id,
            ]);

            ActivityLog::create([
                "user_id"    => $user->id,
                "activity"   => $user->email . " mengkonfirmasi pembayaran booking " . $penyewaan->kode_penyewaan,
                "status"     => "success",
                "ip_address" => $request->ip(),
                "user_agent" => $request->userAgent(),
            ]);

            DB::commit();
            return redirect()->back()->with("success", "Pembayaran berhasil dikonfirmasi.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Gagal konfirmasi pembayaran: " . $e->getMessage());
            return redirect()->back()->with("error", "Gagal konfirmasi pembayaran: " . $e->getMessage());
        }
    }

    /**
     * Aksi 3: Kendaraan diambil (On Rent).
     * Status: approved (paid) → rented
     */
    public function onRent(Request $request, string $id)
    {
        DB::beginTransaction();
        $user = Auth::user();

        try {
            $penyewaan = Penyewaan::findOrFail($id);

            if ($penyewaan->status !== "approved") {
                throw new \Exception("Booking harus berstatus 'approved'.");
            }

            if ($penyewaan->payment_status !== "paid") {
                throw new \Exception("Pembayaran harus lunas sebelum kendaraan diambil.");
            }

            $penyewaan->update([
                "status"     => "rented",
                "petugas_id" => $user->id,
            ]);

            ActivityLog::create([
                "user_id"    => $user->id,
                "activity"   => $user->email . " menandai kendaraan diambil untuk booking " . $penyewaan->kode_penyewaan,
                "status"     => "success",
                "ip_address" => $request->ip(),
                "user_agent" => $request->userAgent(),
            ]);

            DB::commit();
            return redirect()->back()->with("success", "Kendaraan berhasil ditandai sudah diambil.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Gagal tandai kendaraan diambil: " . $e->getMessage());
            return redirect()->back()->with("error", "Gagal: " . $e->getMessage());
        }
    }

    /**
     * Hapus permanen (hanya completed).
     */
    public function destroy(Request $request, string $id)
    {
        DB::beginTransaction();
        $user = Auth::user();

        try {
            $penyewaan = Penyewaan::findOrFail($id);

            if ($penyewaan->status !== "completed") {
                throw new \Exception("Hanya penyewaan dengan status 'completed' yang boleh dihapus permanen.");
            }

            $kode = $penyewaan->kode_penyewaan;
            $penyewaan->delete();

            ActivityLog::create([
                "user_id"    => $user->id,
                "activity"   => $user->email . " menghapus permanen data penyewaan dengan kode " . $kode,
                "status"     => "success",
                "ip_address" => $request->ip(),
                "user_agent" => $request->userAgent(),
            ]);

            DB::commit();
            return redirect()->route("petugas.penyewaan.list")
                ->with("success", "Data penyewaan berhasil dihapus permanen.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Gagal menghapus penyewaan: " . $e->getMessage());
            return redirect()->back()->with("error", "Gagal menghapus penyewaan: " . $e->getMessage());
        }
    }

    /**
     * Helper: ubah status generik (untuk approve).
     */
    private function changeStatus(Request $request, string $id, string $status, string $label)
    {
        DB::beginTransaction();
        $user = Auth::user();

        try {
            $penyewaan = Penyewaan::findOrFail($id);

            if ($penyewaan->status !== "padding") {
                throw new \Exception("Hanya booking dengan status 'padding' yang bisa diproses.");
            }

            $penyewaan->update([
                "status"     => $status,
                "petugas_id" => $user->id,
            ]);

            ActivityLog::create([
                "user_id"    => $user->id,
                "activity"   => $user->email . " " . $label . " " . $penyewaan->kode_penyewaan,
                "status"     => "success",
                "ip_address" => $request->ip(),
                "user_agent" => $request->userAgent(),
            ]);

            DB::commit();
            return redirect()->back()->with("success", "Booking berhasil diproses.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Gagal proses booking: " . $e->getMessage());
            return redirect()->back()->with("error", "Gagal: " . $e->getMessage());
        }
    }
}
