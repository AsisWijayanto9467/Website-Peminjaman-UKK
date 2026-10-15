<?php

namespace App\Http\Controllers;

use App\Models\Penyewaan;
use Illuminate\Http\Request;

class AdminPenyewaanController extends Controller
{
    public function index(Request $request)
    {
        // Hitung statistik untuk cards atas
        $statPending = Penyewaan::where('status', 'padding')->count();
        $statApproved = Penyewaan::where('status', 'approved')->count();
        $statOnRent = Penyewaan::where('status', 'rented')->count();
        $statCompleted = Penyewaan::where('status', 'completed')->count();
        $statCancelled = Penyewaan::where('status', 'cancelled')->count();

        // Query utama dengan filter
        $query = Penyewaan::with('user');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('kode_penyewaan', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('pickup_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('return_date', '<=', $request->date_to);
        }

        $penyewaans = $query->latest()->paginate(10)->withQueryString();

        return view("UserRole.admin.booking.index", compact(
            'penyewaans',
            'statPending',
            'statApproved',
            'statOnRent',
            'statCompleted',
            'statCancelled'
        ));
    }
}
