<?php

namespace App\Http\Controllers;

use App\Exports\LaporanExport;
use App\Exports\PenyewaanExport;
use App\Exports\RingkasanExport;
use App\Models\Denda;
use App\Models\Penyewaan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $dateFrom = $request->filled("date_from")
            ? $request->date_from
            : Carbon::now()->startOfMonth()->toDateString();

        $dateTo = $request->filled("date_to")
            ? $request->date_to
            : Carbon::now()->endOfMonth()->toDateString();

        $status = $request->status;

        $query = Penyewaan::with(["detailPenyewaans.peralatan", "user"])
            ->whereBetween("created_at", [
                Carbon::parse($dateFrom)->startOfDay(),
                Carbon::parse($dateTo)->endOfDay(),
            ]);

        if ($status) {
            $query->where("status", $status);
        }

        $penyewaans = $query->orderBy("created_at", "desc")->get();
        $summary    = $this->hitungSummary($dateFrom, $dateTo, $status);

        return view("UserRole.petugas.report.index",
            compact("penyewaans", "summary", "dateFrom", "dateTo", "status"));
    }

    public function export(Request $request)
    {
        try {
            $dateFrom = $request->filled("date_from")
                ? $request->date_from
                : Carbon::now()->startOfMonth()->toDateString();

            $dateTo = $request->filled("date_to")
                ? $request->date_to
                : Carbon::now()->endOfMonth()->toDateString();

            $status = $request->status;

            $query = Penyewaan::with(["detailPenyewaans.peralatan", "user"])
                ->whereBetween("created_at", [
                    Carbon::parse($dateFrom)->startOfDay(),
                    Carbon::parse($dateTo)->endOfDay(),
                ]);

            if ($status) {
                $query->where("status", $status);
            }

            $penyewaans = $query->orderBy("created_at", "asc")->get();

            $summary = $this->hitungSummary($dateFrom, $dateTo, $status);
            $summary["periode"] = Carbon::parse($dateFrom)->format("d/m/Y")
                . " - "
                . Carbon::parse($dateTo)->format("d/m/Y");

            $filename = "laporan-penyewaan-"
                . Carbon::parse($dateFrom)->format("Ymd")
                . "-"
                . Carbon::parse($dateTo)->format("Ymd")
                . ".xlsx";

            return Excel::download(
                new LaporanExport($penyewaans, $summary, $dateFrom, $dateTo),
                $filename
            );
        } catch (\Exception $e) {
            Log::error("Gagal export laporan: " . $e->getMessage());
            return redirect()->back()->with("error", "Gagal export laporan: " . $e->getMessage());
        }
    }

    private function hitungSummary($dateFrom, $dateTo, $status = null)
    {
        $baseQuery = Penyewaan::whereBetween("created_at", [
            Carbon::parse($dateFrom)->startOfDay(),
            Carbon::parse($dateTo)->endOfDay(),
        ]);

        if ($status) {
            $baseQuery->where("status", $status);
        }

        $totalBooking    = (clone $baseQuery)->count();
        $totalCompleted  = (clone $baseQuery)->where("status", "completed")->count();
        $totalCancelled  = (clone $baseQuery)->where("status", "cancelled")->count();
        $totalDenda      = (clone $baseQuery)->where("status", "denda")->count();

        $totalPendapatan = (clone $baseQuery)
            ->where("payment_status", "paid")
            ->sum("total_price");

        $penyewaanIds = (clone $baseQuery)->pluck("id")->toArray();
        $totalNominalDenda = Denda::whereIn("penyewaan_id", $penyewaanIds)->sum("nominal");

        return [
            "total_booking"       => $totalBooking,
            "total_completed"     => $totalCompleted,
            "total_cancelled"     => $totalCancelled,
            "total_denda"         => $totalDenda,
            "total_pendapatan"    => $totalPendapatan,
            "total_nominal_denda" => $totalNominalDenda,
            "total_bersih"        => $totalPendapatan + $totalNominalDenda,
        ];
    }
}
