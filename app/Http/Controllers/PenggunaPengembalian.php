<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Pengembalian;
use App\Models\Penyewaan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PenggunaPengembalian extends Controller
{
    /**
     * Form pengembalian untuk satu penyewaan.
     */
    public function index(Request $request, string $id)
    {
        $user = Auth::user();

        $penyewaan = Penyewaan::with("detailPenyewaans.peralatan")
            ->where("user_id", $user->id)
            ->where("id", $id)
            ->firstOrFail();

        // Validasi: hanya boleh ajukan saat status "rented"
        if ($penyewaan->status !== "rented") {
            return redirect()->route("booking.list", Auth::id())
                ->with("error", "Pengembalian hanya bisa diajukan untuk penyewaan yang sedang berjalan.");
        }

        // Cegah duplikat pengajuan yang masih pending
        $existing = Pengembalian::where("penyewaan_id", $penyewaan->id)
            ->where("status", "padding")
            ->exists();

        if ($existing) {
            return redirect()->route("booking.list", Auth::id())
                ->with("error", "Anda sudah mengajukan pengembalian. Menunggu konfirmasi petugas.");
        }

        $returnDate = Carbon::parse($penyewaan->return_date);

        return view("UserRole.pengguna.pengembalian.index", compact("penyewaan", "returnDate"));
    }

    /**
     * Simpan pengajuan pengembalian.
     * - Menyimpan data pengembalian dengan status "padding"
     * - Update status penyewaan dari "rented" → "checking"
     */
    public function store(Request $request, string $id)
    {
        DB::beginTransaction();
        $user = Auth::user();

        try {
            // ================= Validasi =================
            $request->validate([
                "photo_prove"      => "nullable|image|mimes:jpg,jpeg,png|max:2048",
                "catatan_pengguna" => "nullable|string|max:1000",
            ], [
                "photo_prove.image" => "File harus berupa gambar.",
                "photo_prove.mimes" => "Format gambar harus jpg, jpeg, atau png.",
                "photo_prove.max"   => "Ukuran gambar maksimal 2 MB.",
            ]);

            // ================= Ambil penyewaan =================
            $penyewaan = Penyewaan::where("user_id", $user->id)
                ->where("id", $id)
                ->firstOrFail();

            // Validasi status
            if ($penyewaan->status !== "rented") {
                throw new \Exception("Pengembalian hanya bisa diajukan untuk penyewaan yang sedang berjalan.");
            }

            // Cegah duplikat
            $existing = Pengembalian::where("penyewaan_id", $penyewaan->id)
                ->where("status", "padding")
                ->exists();

            if ($existing) {
                throw new \Exception("Anda sudah mengajukan pengembalian untuk booking ini.");
            }

            // ================= Upload Foto Bukti =================
            $photoName = null;
            if ($request->hasFile("photo_prove")) {
                $file = $request->file("photo_prove");
                $photoName = time() . "-return-" . uniqid() . "." . $file->getClientOriginalExtension();

                $destinationPath = public_path("uploads/pengembalian/prove");
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }
                $file->move($destinationPath, $photoName);
            }

            // ================= Simpan Pengembalian =================
            Pengembalian::create([
                "user_id"            => $user->id,
                "penyewaan_id"       => $penyewaan->id,
                "petugas_id"         => null,
                "actual_return_date" => now()->toDateString(),
                "status"             => "padding",
                "photo_prove"        => $photoName,
                "catatan_pengguna"   => $request->catatan_pengguna,
                "catatan_petugas"    => null,
                "tanggal_pengajuan"  => now()->toDateString(),
                "tanggal_konfirmasi" => null,
            ]);

            // ================= Update status penyewaan → checking =================
            $penyewaan->update([
                "status" => "checking",
            ]);

            // ================= Log =================
            ActivityLog::create([
                "user_id"    => $user->id,
                "activity"   => $user->email . " mengajukan pengembalian untuk booking " . $penyewaan->kode_penyewaan . " (status penyewaan → checking)",
                "status"     => "success",
                "ip_address" => $request->ip(),
                "user_agent" => $request->userAgent(),
            ]);

            DB::commit();

            return redirect()->route("booking.list", Auth::id())
                ->with("success", "Pengajuan pengembalian berhasil dikirim. Menunggu verifikasi petugas.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Gagal mengajukan pengembalian: " . $e->getMessage());

            ActivityLog::create([
                "user_id"    => $user->id ?? null,
                "activity"   => ($user->email ?? 'Guest') . " gagal mengajukan pengembalian karena " . $e->getMessage(),
                "status"     => "failed",
                "ip_address" => $request->ip(),
                "user_agent" => $request->userAgent(),
            ]);

            return redirect()->back()->withInput()
                ->with("error", "Gagal mengajukan pengembalian: " . $e->getMessage());
        }
    }
}
