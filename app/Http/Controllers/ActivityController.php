<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function index(Request $request) {
        $activityLog =  ActivityLog::with("user")->get();

        return view("UserRole.admin.activitylog.index", compact("activityLog"));
    }
}
