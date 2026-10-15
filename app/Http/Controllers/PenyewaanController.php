<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\DetailPenyewaan;
use App\Models\Keranjang;
use App\Models\Penyewaan;
use App\Models\Peralatan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PenyewaanController extends Controller
{
    public function keranjang()
    {
        $user = Auth::user();
        $keranjangs = Keranjang::with('peralatan.category')
            ->where('user_id', $user->id)
            ->get();

        return view("UserRole.pengguna.transaction.keranjang", compact('keranjangs'));
    }

    public function qr(string $id)
    {
        $penyewaan = Penyewaan::findOrFail($id);
        return view("UserRole.pengguna.History.qr", compact("penyewaan"));
    }

    public function booking()
    {
        return view("UserRole.pengguna.transaction.booking");
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        $user = Auth::user();

        try {
            $request->validate([
                "kode_penyewaan"    => "required|unique:penyewaans,kode_penyewaan",
                "pickup_date"       => "required|date",
                "return_date"       => "required|date|after_or_equal:pickup_date",
                "customer_name"     => "required|string|max:255",
                "customer_address"  => "required|string",
                "customer_id_card"  => "required|string|max:50",
                "face_photo"        => "nullable|image|mimes:jpg,jpeg,png|max:2048",
                "id_card_photo"     => "nullable|image|mimes:jpg,jpeg,png|max:2048",
            ]);

            // ================= Ambil isi keranjang user =================
            $keranjang = Keranjang::with("peralatan")
                ->where("user_id", $user->id)
                ->get();

            if ($keranjang->isEmpty()) {
                throw new \Exception("Keranjang Anda kosong, silakan tambahkan peralatan terlebih dahulu.");
            }

            // ================= Hitung Durasi Hari =================
            $pickupDate = Carbon::parse($request->pickup_date);
            $returnDate = Carbon::parse($request->return_date);
            $durasiHari = $pickupDate->diffInDays($returnDate) ?: 1;

            // ================= Hitung Total Price & Detail =================
            $totalPrice = 0;
            $details    = [];

            foreach ($keranjang as $item) {
                $hargaPerHari = optional($item->peralatan)->price_per_day ?? 0;
                $jumlah       = (int) $item->jumlah;

                // subtotal = harga per hari × jumlah × durasi hari
                $subtotal = $hargaPerHari * $jumlah * $durasiHari;

                // Kalau Anda mau subtotal hanya harian × jumlah (tanpa durasi):
                // $subtotal = $hargaPerHari * $jumlah;

                $totalPrice += $subtotal;

                $details[] = [
                    "penyewaan_id"  => null, // diisi setelah penyewaan dibuat
                    "peralatan_id"  => $item->peralatan_id,
                    "price_per_day" => $hargaPerHari,
                    "subtotal"      => $subtotal,     // ← FIX: sekarang diisi
                    "durasi_hari"   => $durasiHari,
                    "jumlah"        => $jumlah,
                    "created_at"    => now(),
                    "updated_at"    => now(),
                ];
            }

            // ================= Upload Foto =================
            $facePhotoName = null;
            if ($request->hasFile("face_photo")) {
                $file = $request->file("face_photo");
                $facePhotoName = time() . "-face-" . uniqid() . "." . $file->getClientOriginalExtension();
                $destinationPath = public_path("uploads/penyewaan/face");
                if (!file_exists($destinationPath)) mkdir($destinationPath, 0777, true);
                $file->move($destinationPath, $facePhotoName);
            }

            $idCardPhotoName = null;
            if ($request->hasFile("id_card_photo")) {
                $file = $request->file("id_card_photo");
                $idCardPhotoName = time() . "-ktp-" . uniqid() . "." . $file->getClientOriginalExtension();
                $destinationPath = public_path("uploads/penyewaan/ktp");
                if (!file_exists($destinationPath)) mkdir($destinationPath, 0777, true);
                $file->move($destinationPath, $idCardPhotoName);
            }

            // ================= Simpan Penyewaan =================
            $penyewaan = Penyewaan::create([
                "user_id"          => $user->id,
                "petugas_id"       => null,
                "kode_penyewaan"   => $request->kode_penyewaan,
                "pickup_date"      => $request->pickup_date,
                "return_date"      => $request->return_date,
                "status"           => "padding",
                "payment_status"   => "unpaid",
                "total_price"      => $totalPrice,
                "customer_name"    => $request->customer_name,
                "customer_address" => $request->customer_address,
                "customer_id_card" => $request->customer_id_card,
                "face_photo"       => $facePhotoName,
                "id_card_photo"    => $idCardPhotoName,
                "payment_photo"    => null,
            ]);

            // ================= Pindahkan Keranjang → DetailPenyewaan =================
            foreach ($details as &$d) {
                $d["penyewaan_id"] = $penyewaan->id;
            }
            unset($d);

            DetailPenyewaan::insert($details);

            // ================= Hapus isi keranjang user =================
            Keranjang::where("user_id", $user->id)->delete();

            // ================= Log Aktivitas =================
            ActivityLog::create([
                "user_id"    => $user->id,
                "activity"   => $user->email . " berhasil menambahkan data penyewaan baru",
                "status"     => "success",
                "ip_address" => $request->ip(),
                "user_agent" => $request->userAgent()
            ]);

            DB::commit();

            return redirect()->route("penyewaan.booking")
                ->with("success", "Checkout berhasil! Pengajuan penyewaan telah dibuat.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Terjadi kesalahan: " . $e->getMessage());

            ActivityLog::create([
                "user_id"    => $user->id ?? null,
                "activity"   => ($user->email ?? 'Guest') . " gagal menambahkan data penyewaan karena " . $e->getMessage(),
                "status"     => "failed",
                "ip_address" => $request->ip(),
                "user_agent" => $request->userAgent()
            ]);

            return redirect()->back()->withInput()
                ->with("error", "Gagal menambahkan data penyewaan karena error: " . $e->getMessage());
        }
    }

    public function updateQty(Request $request, $id, $action)
    {
        DB::beginTransaction();
        try {
            $keranjang = Keranjang::with('peralatan')->findOrFail($id);

            if ($keranjang->user_id !== Auth::id()) {
                return redirect()->back()->with('error', 'Akses ditolak.');
            }

            $peralatan = $peralatan = $keranjang->peralatan;

            if ($action === 'tambah') {
                if ($peralatan->stok_tersedia < 1) {
                    return redirect()->back()->with('error', 'Stok peralatan habis.');
                }
                $keranjang->increment('jumlah');
                $peralatan->decrement('stok_tersedia');
            } elseif ($action === 'kurang') {
                if ($keranjang->jumlah > 1) {
                    $keranjang->decrement('jumlah');
                    $peralatan->increment('stok_tersedia');
                } else {
                    // Jika jumlah 1 lalu dikurangi, hapus item dan kembalikan stok
                    $peralatan->increment('stok_tersedia', $keranjang->jumlah);
                    $keranjang->delete();
                }
            }

            DB::commit();
            return redirect()->route('keranjang.index')->with('success', 'Keranjang berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Gagal update keranjang: " . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem.');
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $keranjang = Keranjang::with('peralatan')->findOrFail($id);

            if ($keranjang->user_id !== Auth::id()) {
                return redirect()->back()->with('error', 'Akses ditolak.');
            }

            // Kembalikan stok peralatan
            $keranjang->peralatan->increment('stok_tersedia', $keranjang->jumlah);

            // Hapus item keranjang
            $keranjang->delete();

            DB::commit();
            return redirect()->route('keranjang.index')->with('success', 'Item berhasil dihapus dari keranjang.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Gagal hapus keranjang: " . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem.');
        }
    }
}
