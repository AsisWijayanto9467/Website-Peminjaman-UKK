<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = User::all();
        return view('UserRole.admin.users.index', compact("user"));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view("UserRole.admin.users.create");
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
                "name" => "required",
                "email" => "required|email",
                "password" => "required|min:6|max:20",
                "no_hp" => "required",
                "role" => "required|in:pengguna,petugas,admin",
                "photo_profile" => "nullable|max:2048",
            ]);

            $photoProfileName = null;

            if ($request->hasFile("photo_profile")) {
                $file = $request->file("photo_profile");
                $photoProfileName = time() . "-" . uniqid() . "." . $file->getClientOriginalExtension();

                $directionPath = public_path("uploads/photo_profile");
                if (!file_exists($directionPath)) {
                    mkdir($directionPath, 0777, true);
                }
                $file->move($directionPath, $photoProfileName);
            }

            User::create([
                "name" => $request->name,
                "email" => $request->email,
                "password" => Hash::make($request->password),
                "no_hp" => $request->no_hp,
                "role" => $request->role,
                "photo_profile" => $photoProfileName,
            ]);

            ActivityLog::create([
                "user_id" => $admins->id,
                "activity" => $admins->email . " berhasil membuat akun",
                "status" => "success",
                "ip_address" => $request->ip(),
                "user_agent" => $request->userAgent()
            ]);

            DB::commit();

            return redirect()->route("user.list")->with("success", "Anda berhasil membuat akun");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Terjadi kesalahan pada code di bagian ini " . $e->getMessage());

            ActivityLog::create([
                "user_id" => null,
                "activity" => $admins->email . " gagal membuat akun karena " . $e->getMessage(),
                "status" => "failed",
                "ip_address" => $request->ip(),
                "user_agent" => $request->userAgent()
            ]);

            return redirect()->back()->with("error", $admins->email . " gagal membuat akun karena error ini " . $e->getMessage());
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
        $user = User::findOrFail($id);
        return view("UserRole.admin.users.edit", compact("user"));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        DB::beginTransaction();
        $admins = Auth::user();
        $user = User::findOrFail($id);
        try {
            $request->validate([
                "name" => "required",
                "email" => "required|email",
                "password" => "nullable|min:6|max:20",
                "no_hp" => "required",
                "role" => "required|in:pengguna,petugas,admin",
                "photo_profile" => "nullable|max:2048",
            ]);

            $data = $request->only(["name", "email", "no_hp", "role"]);

            if ($request->filled("password")) {
                $data["password"] = Hash::make($request->password);
            }

            $photoProfileName = null;

            if ($request->hasFile("photo_profile")) {
                if ($user->photo_profile && file_exists(public_path("uploads/photo_profile/" . $user->photo_profile))) {
                    unlink(public_path("uploads/photo_profile/" . $user->photo_profile));
                }

                $file = $request->file("photo_profile");
                $photoProfileName = time() . "-" . uniqid() . "." . $file->getClientOriginalExtension();
                $data["photo_profile"] = $photoProfileName;

                $directionPath = public_path("uploads/photo_profile");
                if (!file_exists($directionPath)) {
                    mkdir($directionPath, 0777, true);
                }
                $file->move($directionPath, $photoProfileName);
            }

            $user->update($data);

            ActivityLog::create([
                "user_id" => $admins->id,
                "activity" => $admins->email . " berhasil mengupdate akun",
                "status" => "success",
                "ip_address" => $request->ip(),
                "user_agent" => $request->userAgent()
            ]);

            DB::commit();

            return redirect()->route("user.list")->with("success", "Anda berhasil mengupdate akun");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Terjadi kesalahan pada code di bagian ini " . $e->getMessage());

            ActivityLog::create([
                "user_id" => null,
                "activity" => $admins->email . " gagal mengupdate akun karena " . $e->getMessage(),
                "status" => "failed",
                "ip_address" => $request->ip(),
                "user_agent" => $request->userAgent()
            ]);

            return redirect()->back()->with("error", $admins->email . " gagal mengupdate akun karena error ini " . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        DB::beginTransaction();
        $admins = Auth::user();
        $user = User::findOrFail($id);
        try {
            if ($user->photo_profile && file_exists(public_path("uploads/photo_profile/" . $user->photo_profile))) {
                unlink(public_path("uploads/photo_profile/" . $user->photo_profile));
            }

            $user->delete();

            ActivityLog::create([
                "user_id" => $admins->id,
                "activity" => $admins->email . " berhasil menghapus akun",
                "status" => "success",
                "ip_address" => $request->ip(),
                "user_agent" => $request->userAgent()
            ]);

            DB::commit();

            return redirect()->route("user.list")->with("success", "Anda berhasil menghapus akun");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Terjadi kesalahan pada code di bagian ini " . $e->getMessage());

            ActivityLog::create([
                "user_id" => null,
                "activity" => $admins->email . " gagal menghapus akun karena " . $e->getMessage(),
                "status" => "failed",
                "ip_address" => $request->ip(),
                "user_agent" => $request->userAgent()
            ]);

            return redirect()->back()->with("error", $admins->email . " gagal menghapus akun karena error ini " . $e->getMessage());
        }
    }
}
