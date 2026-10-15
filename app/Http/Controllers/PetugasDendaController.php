<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PetugasDendaController extends Controller
{
    public function index(Request $request) {
        return view("UserRole.petugas.denda.index");
    }

    public function detail(Request $request) {
        return view("UserRole.petugas.denda.setting");
    }
}
