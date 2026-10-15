<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PetugasPenyewaanController extends Controller
{
    public function index(Request $request) {
        return view("UserRole.petugas.penyewaan.index");
    }

    public function detail(Request $request) {
        return view("UserRole.petugas.penyewaan.setting");
    }
}
