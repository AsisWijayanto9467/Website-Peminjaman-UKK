<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\DetailPenyewaan;
use App\Models\Keranjang;
use App\Models\Penyewaan;
use Carbon\Carbon;
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
            $penyewaan = Penyewaan::with("detailPenyewaans.peralatan")
                ->where("user_id", $user->id)
                ->where("id", $id)
                ->firstOrFail();

            if ($penyewaan->status !== "padding") {
                throw new \Exception("Hanya penyewaan dengan status 'padding' yang bisa dibatalkan.");
            }

            foreach ($penyewaan->detailPenyewaans as $detail) {
                if ($detail->peralatan) {
                    $detail->peralatan->update([
                        "stok_tersedia" => $detail->peralatan->stok_tersedia + $detail->jumlah,
                    ]);
                }
            }

            DetailPenyewaan::where("penyewaan_id", $penyewaan->id)->delete();
            Keranjang::where("user_id", $user->id)->delete();

            $penyewaan->update(["status" => "cancelled"]);

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
            Log::error("Gagal membatalkan penyewaan: " . $e->getMessage());

            ActivityLog::create([
                "user_id"    => $user->id ?? null,
                "activity"   => ($user->email ?? 'Guest') . " gagal membatalkan penyewaan karena " . $e->getMessage(),
                "status"     => "failed",
                "ip_address" => $request->ip(),
                "user_agent" => $request->userAgent(),
            ]);

            return redirect()->back()->with("error", "Gagal membatalkan penyewaan: " . $e->getMessage());
        }
    }

    /**
     * Halaman upload bukti pembayaran.
     */
    public function payment(Request $request, string $id)
    {
        $user = Auth::user();

        $penyewaan = Penyewaan::with("detailPenyewaans.peralatan")
            ->where("user_id", $user->id)
            ->where("id", $id)
            ->firstOrFail();

        // Validasi status: hanya boleh upload saat approved
        if ($penyewaan->status !== "approved") {
            return redirect()->route("booking.list")
                ->with("error", "Upload bukti pembayaran hanya bisa dilakukan untuk booking yang sudah disetujui.");
        }

        // Hitung durasi
        $pickup = Carbon::parse($penyewaan->pickup_date);
        $return = Carbon::parse($penyewaan->return_date);
        $durasi = $pickup->diffInDays($return) ?: 1;

        return view("UserRole.pengguna.History.payment", compact("penyewaan", "durasi"));
    }

    /**
     * Proses upload bukti pembayaran.
     */
    public function uploadPayment(Request $request, string $id)
    {
        DB::beginTransaction();
        $user = Auth::user();

        try {
            $request->validate([
                "payment_photo" => "required|image|mimes:jpg,jpeg,png|max:2048",
            ], [
                "payment_photo.required" => "Bukti pembayaran wajib diunggah.",
                "payment_photo.image"    => "File harus berupa gambar.",
                "payment_photo.mimes"    => "Format gambar harus jpg, jpeg, atau png.",
                "payment_photo.max"      => "Ukuran gambar maksimal 2MB.",
            ]);

            $penyewaan = Penyewaan::where("user_id", $user->id)
                ->where("id", $id)
                ->firstOrFail();

            if ($penyewaan->status !== "approved") {
                throw new \Exception("Upload bukti pembayaran hanya bisa dilakukan untuk booking yang sudah disetujui.");
            }

            // ================= Upload Foto =================
            $paymentPhotoName = null;
            if ($request->hasFile("payment_photo")) {
                $file = $request->file("payment_photo");
                $paymentPhotoName = time() . "-payment-" . uniqid() . "." . $file->getClientOriginalExtension();

                $destinationPath = public_path("uploads/penyewaan/payment");
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }

                // Hapus file lama kalau ada (re-upload)
                if ($penyewaan->payment_photo && file_exists($destinationPath . "/" . $penyewaan->payment_photo)) {
                    @unlink($destinationPath . "/" . $penyewaan->payment_photo);
                }

                $file->move($destinationPath, $paymentPhotoName);
            }

            // ================= Update Penyewaan =================
            $penyewaan->update([
                "payment_photo" => $paymentPhotoName,
                // payment_status tetap 'unpaid' sampai petugas konfirmasi
            ]);

            ActivityLog::create([
                "user_id"    => $user->id,
                "activity"   => $user->email . " mengunggah bukti pembayaran untuk booking " . $penyewaan->kode_penyewaan,
                "status"     => "success",
                "ip_address" => $request->ip(),
                "user_agent" => $request->userAgent(),
            ]);

            DB::commit();

            return redirect()->route("booking.list", Auth::user()->id)
                ->with("success", "Bukti pembayaran berhasil diunggah. Menunggu konfirmasi petugas.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Gagal upload bukti pembayaran: " . $e->getMessage());

            ActivityLog::create([
                "user_id"    => $user->id ?? null,
                "activity"   => ($user->email ?? 'Guest') . " gagal upload bukti pembayaran karena " . $e->getMessage(),
                "status"     => "failed",
                "ip_address" => $request->ip(),
                "user_agent" => $request->userAgent(),
            ]);

            return redirect()->back()->withInput()
                ->with("error", "Gagal upload bukti pembayaran: " . $e->getMessage());
        }
    }

    /**
     * Form bayar denda untuk pengguna.
     */
    public function formDenda(Request $request, string $id)
    {
        $user = Auth::user();

        $denda = \App\Models\Denda::with([
            "penyewaan.detailPenyewaans.peralatan",
            "pengembalian",
        ])
            ->where("user_id", $user->id)
            ->where("id", $id)
            ->firstOrFail();

        // Validasi: hanya denda belum dibayar
        if ($denda->status === "sudah dibayar") {
            return redirect()->route("booking.list", Auth::id())
                ->with("error", "Denda ini sudah dibayar. Tidak perlu upload ulang.");
        }

        // Cegah upload ulang kalau sudah ada bukti (opsional)
        // Kalau mau user bisa ganti bukti, komentar baris di bawah
        // if ($denda->bukti_dibayar) {
        //     return redirect()->route("booking.list", Auth::id())
        //         ->with("error", "Bukti pembayaran sudah diunggah. Menunggu konfirmasi petugas.");
        // }

        return view("UserRole.pengguna.History.dendaForm", compact("denda"));
    }

    /**
     * Proses upload bukti bayar denda.
     */
    public function uploadDenda(Request $request, string $id)
    {
        DB::beginTransaction();
        $user = Auth::user();

        try {
            $request->validate([
                "bukti_dibayar" => "required|image|mimes:jpg,jpeg,png|max:2048",
            ], [
                "bukti_dibayar.required" => "Bukti pembayaran denda wajib diunggah.",
                "bukti_dibayar.image"    => "File harus berupa gambar.",
                "bukti_dibayar.mimes"    => "Format gambar harus jpg, jpeg, atau png.",
                "bukti_dibayar.max"      => "Ukuran gambar maksimal 2 MB.",
            ]);

            $denda = \App\Models\Denda::where("user_id", $user->id)
                ->where("id", $id)
                ->firstOrFail();

            if ($denda->status === "sudah dibayar") {
                throw new \Exception("Denda ini sudah dibayar. Tidak bisa upload ulang.");
            }

            // ================= Upload Foto =================
            $buktiName = null;
            if ($request->hasFile("bukti_dibayar")) {
                $file = $request->file("bukti_dibayar");
                $buktiName = time() . "-denda-" . uniqid() . "." . $file->getClientOriginalExtension();

                $destinationPath = public_path("uploads/denda/bukti");
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }

                // Hapus file lama kalau ada (re-upload)
                if ($denda->bukti_dibayar && file_exists($destinationPath . "/" . $denda->bukti_dibayar)) {
                    @unlink($destinationPath . "/" . $denda->bukti_dibayar);
                }

                $file->move($destinationPath, $buktiName);
            }

            // ================= Update Denda =================
            $denda->update([
                "bukti_dibayar" => $buktiName,
                // status tetap "belum dibayar" sampai petugas konfirmasi
            ]);

            ActivityLog::create([
                "user_id"    => $user->id,
                "activity"   => $user->email . " mengunggah bukti pembayaran denda " . $denda->kode_denda,
                "status"     => "success",
                "ip_address" => $request->ip(),
                "user_agent" => $request->userAgent(),
            ]);

            DB::commit();

            return redirect()->route("booking.list", Auth::id())
                ->with("success", "Bukti pembayaran denda berhasil diunggah. Menunggu konfirmasi petugas.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Gagal upload bukti denda: " . $e->getMessage());

            ActivityLog::create([
                "user_id"    => $user->id ?? null,
                "activity"   => ($user->email ?? 'Guest') . " gagal upload bukti denda karena " . $e->getMessage(),
                "status"     => "failed",
                "ip_address" => $request->ip(),
                "user_agent" => $request->userAgent(),
            ]);

            return redirect()->back()->withInput()
                ->with("error", "Gagal upload bukti denda: " . $e->getMessage());
        }
    }
}
