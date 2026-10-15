<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PetugasPengembalianController extends Controller
{
    public function index(Request $request) {
        return view("UserRole.petugas.pengembalian.index");
    }

    public function detail(Request $request) {
        return view("UserRole.petugas.pengembalian.setting");
    }
}
