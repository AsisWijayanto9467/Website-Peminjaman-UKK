<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function index(Request $request) {
        return view("UserRole.petugas.report.index");
    }
}
