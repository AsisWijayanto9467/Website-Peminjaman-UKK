<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BerandaController extends Controller
{
    public function beranda(Request $request) {
        return view("UserRole.pengguna.beranda");
    }
}
