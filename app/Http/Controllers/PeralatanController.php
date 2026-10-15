<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Keranjang;
use App\Models\Peralatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PeralatanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $peralatan = Peralatan::where("status", "aktif")->get();
        return view("UserRole.admin.peralatan.index", compact("peralatan"));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $category = Category::where("status", "aktif")->get();
        return view("UserRole.admin.peralatan.create", compact("category"));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        $admins = Auth::user();

        try {
            $request->validate([
                "category_id" => "required",
                "kode_barang" => "required",
                "nama_barang" => "required",
                "deskripsi" => "required",
                "price_per_day" => "required",
                "stok_total" => "required",
                "price_denda_perhari" => "required",
                "status" => "required",
                "gambar_peralatan" => "required",
            ]);

            $gambarPeralatanName = null;

            if ($request->hasFile("gambar_peralatan")) {
                $file = $request->file("gambar_peralatan");
                $gambarPeralatanName = time() . "-" . uniqid() . "." . $file->getClientOriginalExtension();

                $directionPath = public_path("uploads/gambar_peralatan");
                if (!file_exists($directionPath)) {
                    mkdir($directionPath, 0777, true);
                }
                $file->move($directionPath, $gambarPeralatanName);
            }

            Peralatan::create([
                "category_id" => $request->category_id,
                "kode_barang" => $request->kode_barang,
                "nama_barang" => $request->nama_barang,
                "deskripsi" => $request->deskripsi,
                "price_per_day" => $request->price_per_day,
                "stok_total" => $request->stok_total,
                "stok_tersedia" => $request->stok_total,
                "price_denda_perhari" => $request->price_denda_perhari,
                "status" => $request->status,
                "gambar_peralatan" => $gambarPeralatanName,
            ]);

            ActivityLog::create([
                "user_id" => $admins->id,
                "activity" => $admins->email . " berhasil menambahkan item peralatan",
                "status" => "success",
                "ip_address" => $request->ip(),
                "user_agent" => $request->userAgent()
            ]);

            DB::commit();

            return redirect()->route("peralatan.list")->with("success", "Anda berhasil menambahkan item peralatan");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Terjadi kesalahan pada code di bagian ini " . $e->getMessage());

            ActivityLog::create([
                "user_id" => null,
                "activity" => $admins->email . " gagal menambahkan item peralatan karena " . $e->getMessage(),
                "status" => "failed",
                "ip_address" => $request->ip(),
                "user_agent" => $request->userAgent()
            ]);

            return redirect()->back()->with("error", $admins->email . " gagal menambahkan item peralatan karena error ini " . $e->getMessage());
        }
    }


    

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $category = Category::where("status", "aktif")->get();
        $peralatan = Peralatan::findOrFail($id);
        return view("UserRole.admin.peralatan.edit", compact("category", "peralatan"));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        DB::beginTransaction();
        $admins = Auth::user();
        $peralatan = Peralatan::findOrFail($id);

        try {
            $request->validate([
                "category_id" => "required",
                "kode_barang" => "required",
                "nama_barang" => "required",
                "deskripsi" => "required",
                "price_per_day" => "required",
                "stok_total" => "required",
                "price_denda_perhari" => "required",
                "status" => "required",
                "gambar_peralatan" => "nullable",
            ]);

            $data = $request->only([
                        "category_id",
                        "kode_barang",
                        "nama_barang",
                        "deskripsi",
                        "price_per_day",
                        "stok_total",
                        "price_denda_perhari",
                        "status",
                    ]);

            $data["stok_tersedia"] = $request->stok_total;

            $gambarPeralatanName = null;

            if ($request->hasFile("gambar_peralatan")) {
                if($peralatan->gambar_peralatan && file_exists(public_path("uploads/gambar_peralatan/" . $peralatan->gambar_peralatan))) {
                    unlink(public_path("uploads/gambar_peralatan/" . $peralatan->gambar_peralatan));
                }

                $file = $request->file("gambar_peralatan");
                $gambarPeralatanName = time() . "-" . uniqid() . "." . $file->getClientOriginalExtension();
                $data["gambar_peralatan"] = $gambarPeralatanName;

                $directionPath = public_path("uploads/gambar_peralatan");
                if (!file_exists($directionPath)) {
                    mkdir($directionPath, 0777, true);
                }
                $file->move($directionPath, $gambarPeralatanName);
            }

            $peralatan->update($data);

            ActivityLog::create([
                "user_id" => $admins->id,
                "activity" => $admins->email . " berhasil mengupdate item peralatan",
                "status" => "success",
                "ip_address" => $request->ip(),
                "user_agent" => $request->userAgent()
            ]);

            DB::commit();

            return redirect()->route("peralatan.list")->with("success", "Anda berhasil mengupdate item peralatan");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Terjadi kesalahan pada code di bagian ini " . $e->getMessage());

            ActivityLog::create([
                "user_id" => null,
                "activity" => $admins->email . " gagal mengupdate item peralatan karena " . $e->getMessage(),
                "status" => "failed",
                "ip_address" => $request->ip(),
                "user_agent" => $request->userAgent()
            ]);

            return redirect()->back()->with("error", $admins->email . " gagal mengupdate item peralatan karena error ini " . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        DB::beginTransaction();
        $admins = Auth::user();
        $peralatan = Peralatan::findOrFail($id);

        try {
            if ($peralatan->gambar_peralatan && file_exists(public_path("uploads/gambar_peralatan/" . $peralatan->gambar_peralatan))) {
                unlink(public_path("uploads/gambar_peralatan/" . $peralatan->gambar_peralatan));
            }

            $peralatan->delete();

            ActivityLog::create([
                "user_id" => $admins->id,
                "activity" => $admins->email . " berhasil menghapus peralatan",
                "status" => "success",
                "ip_address" => $request->ip(),
                "user_agent" => $request->userAgent()
            ]);

            DB::commit();

            return redirect()->route("peralatan.list")->with("success", "Anda berhasil menghapus peralatan");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Terjadi kesalahan pada code di bagian ini " . $e->getMessage());

            ActivityLog::create([
                "user_id" => null,
                "activity" => $admins->email . " gagal menghapus peralatan karena " . $e->getMessage(),
                "status" => "failed",
                "ip_address" => $request->ip(),
                "user_agent" => $request->userAgent()
            ]);

            return redirect()->back()->with("error", $admins->email . " gagal menghapus peralatan karena error ini " . $e->getMessage());
        }
    }
}
