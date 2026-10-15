<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KategoriController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $kategori = Category::where("status", "aktif")->get();
        return view("UserRole.admin.kategori.index", compact("kategori"));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('UserRole.admin.kategori.create');
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
                "nama_kategori" => "required",
                "deskripsi_kategori" => "required",
                "status" => "required|in:aktif,nonaktif"
            ]);

            Category::create([
                "nama_kategori" => $request->nama_kategori,
                "deskripsi_kategori" => $request->deskripsi_kategori,
                "status" => $request->status,
            ]);

            ActivityLog::create([
                "user_id" => $admins->id,
                "activity" => $admins->email . " berhasil menambah kategori",
                "status" => "success",
                "ip_address" => $request->ip(),
                "user_agent" => $request->userAgent()
            ]);

            DB::commit();

            return redirect()->route("kategori.list")->with("success", "Anda berhasil menambah kategori");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Terjadi kesalahan pada code di bagian ini " . $e->getMessage());

            ActivityLog::create([
                "user_id" => null,
                "activity" => $admins->email . " gagal menambah kategori karena " . $e->getMessage(),
                "status" => "failed",
                "ip_address" => $request->ip(),
                "user_agent" => $request->userAgent()
            ]);

            return redirect()->back()->with("error", $admins->email . " gagal menambah kategori karena error ini " . $e->getMessage());
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
        $kategori = Category::findOrFail($id);
        return view("UserRole.admin.kategori.edit", compact("kategori"));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        DB::beginTransaction();
        $admins = Auth::user();
        $kategori = Category::findOrFail($id);
        try {
            $request->validate([
                "nama_kategori" => "required|unique:categories,nama_kategori",
                "deskripsi_kategori" => "required",
                "status" => "required|in:aktif,nonaktif"
            ]);

            $kategori->update([
                "nama_kategori" => $request->nama_kategori,
                "deskripsi_kategori" => $request->deskripsi_kategori,
                "status" => $request->status,
            ]);

            ActivityLog::create([
                "user_id" => $admins->id,
                "activity" => $admins->email . " berhasil mengupdate kategori",
                "status" => "success",
                "ip_address" => $request->ip(),
                "user_agent" => $request->userAgent()
            ]);

            DB::commit();

            return redirect()->route("kategori.list")->with("success", "Anda berhasil mengupdate kategori");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Terjadi kesalahan pada code di bagian ini " . $e->getMessage());

            ActivityLog::create([
                "user_id" => null,
                "activity" => $admins->email . " gagal mengupdate kategori karena " . $e->getMessage(),
                "status" => "failed",
                "ip_address" => $request->ip(),
                "user_agent" => $request->userAgent()
            ]);

            return redirect()->back()->with("error", $admins->email . " gagal mengupdate kategori karena error ini " . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        DB::beginTransaction();
        $admins = Auth::user();
        $kategori = Category::findOrFail($id);
        try {
            $kategori->delete();

            ActivityLog::create([
                "user_id" => $admins->id,
                "activity" => $admins->email . " berhasil menghapus kategori",
                "status" => "success",
                "ip_address" => $request->ip(),
                "user_agent" => $request->userAgent()
            ]);

            DB::commit();

            return redirect()->route("kategori.list")->with("success", "Anda berhasil menghapus kategori");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Terjadi kesalahan pada code di bagian ini " . $e->getMessage());

            ActivityLog::create([
                "user_id" => null,
                "activity" => $admins->email . " gagal menghapus kategori karena " . $e->getMessage(),
                "status" => "failed",
                "ip_address" => $request->ip(),
                "user_agent" => $request->userAgent()
            ]);

            return redirect()->back()->with("error", $admins->email . " gagal menghapus kategori karena error ini " . $e->getMessage());
        }
    }
}
