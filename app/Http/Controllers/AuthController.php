<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function showLogin(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();
            if (in_array($user->role, ["admin", "petugas"])) {
                return redirect()->route("admin.dashboard");
            }
            return redirect()->route("pengguna.beranda");
        }
        return view("auth.login");
    }

    public function Login(Request $request)
    {
        DB::beginTransaction();
        try {
            $credential = $request->validate([
                "email" => "required",
                "password" => "required"
            ]);

            if (Auth::attempt($credential)) {
                $request->session()->regenerate();
                $user = Auth::user();

                session([
                    "loggedIn" => true,
                    "name" => $user->name,
                    "email" => $user->email,
                    "role" => $user->role,
                ]);

                ActivityLog::create([
                    "user_id" => $user->id,
                    "activity" => "Berhasil melakukan Login",
                    "status" => "success",
                    "ip_address" => $request->ip(),
                    "user_agent" => $request->userAgent()
                ]);

                DB::commit();

                if (in_array($user->role, ["admin", "petugas"])) {
                    return redirect()->route("admin.dashboard")->with("success", "Anda berhasil melakukan login");
                }
                return redirect()->route("pengguna.beranda")->with("success", "Anda berhasil melakukan login");
            }

            ActivityLog::create([
                "user_id" => null,
                "activity" => "gagal melakukan Login",
                "status" => "failed",
                "ip_address" => $request->ip(),
                "user_agent" => $request->userAgent()
            ]);

            DB::commit();
            return redirect()->back()->with("error", "gagal melakukan login");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Terjadi kesalahan pada code di bagian ini " . $e->getMessage());

            ActivityLog::create([
                "user_id" => null,
                "activity" => "gagal melakukan Login karena " . $e->getMessage(),
                "status" => "failed",
                "ip_address" => $request->ip(),
                "user_agent" => $request->userAgent()
            ]);

            return redirect()->back()->with("error", "gagal melakukan login karena error ini " . $e->getMessage());
        }
    }

    public function showRegister(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();
            if (in_array($user->role, ["admin", "petugas"])) {
                return redirect()->route("admin.dashboard");
            }
            return redirect()->route("pengguna.beranda");
        }
        return view("auth.register");
    }

    public function register(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                "name" => "required",
                "email" => "required|email",
                "password" => "required|min:6|max:15",
                "no_hp" => "required",
            ]);

            $user = User::create([
                "name" => $request->name,
                "email" => $request->email,
                "password" => Hash::make($request->password),
                "no_hp" => $request->no_hp,
                "role" => "pengguna",
            ]);

            Auth::login($user);

            session([
                "loggedIn" => true,
                "name" => $user->name,
                "email" => $user->email,
                "role" => $user->role,
            ]);

            ActivityLog::create([
                "user_id" => $user->id,
                "activity" => "berhasil melakukan register",
                "status" => "success",
                "ip_address" => $request->ip(),
                "user_agent" => $request->userAgent()
            ]);

            DB::commit();

            if (in_array($user->role, ["admin", "petugas"])) {
                return redirect()->route("admin.dashboard")->with("success", "Anda berhasil melakukan register");
            }
            return redirect()->route("pengguna.beranda")->with("success", "Anda berhasil melakukan register");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Terjadi kesalahan pada code di bagian ini " . $e->getMessage());

            ActivityLog::create([
                "user_id" => null,
                "activity" => "gagal melakukan register karena " . $e->getMessage(),
                "status" => "failed",
                "ip_address" => $request->ip(),
                "user_agent" => $request->userAgent()
            ]);

            return redirect()->back()->with("error", "gagal melakukan register karena error ini " . $e->getMessage());
        }
    }


    public function logout(Request $request)
    {
        DB::beginTransaction();
        $user = Auth::user();
        try {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($user) {
                ActivityLog::create([
                    "user_id" => $user->id,
                    "activity" => "berhasil melakukan logout",
                    "status" => "success",
                    "ip_address" => $request->ip(),
                    "user_agent" => $request->userAgent()
                ]);

                DB::commit();
            }

            return redirect()->route("login")->with("success", "Anda berhasil melakukan logout!");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Terjadi kesalahan pada code di bagian ini " . $e->getMessage());

            ActivityLog::create([
                "user_id" => null,
                "activity" => "gagal melakukan logout karena " . $e->getMessage(),
                "status" => "failed",
                "ip_address" => $request->ip(),
                "user_agent" => $request->userAgent()
            ]);

            return redirect()->back()->with("error", "gagal melakukan logout karena error ini " . $e->getMessage());
        }
    }
}
