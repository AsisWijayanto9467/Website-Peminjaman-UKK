<?php

namespace App\Exports;

use App\Models\Denda;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PenyewaanExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    ShouldAutoSize,
    WithTitle
{
    protected $penyewaans;
    protected $dateFrom;
    protected $dateTo;

    public function __construct($penyewaans, $dateFrom = null, $dateTo = null)
    {
        $this->penyewaans = $penyewaans;
        $this->dateFrom   = $dateFrom;
        $this->dateTo     = $dateTo;
    }

    public function collection(): Collection   // ← tambahkan tipe
    {
        return $this->penyewaans;
    }

    public function headings(): array
    {
        return [
            "No",
            "Kode Booking",
            "Tanggal Pesan",
            "Nama Peminjam",
            "No. KTP",
            "Kendaraan",
            "Kode Barang",
            "Jumlah",
            "Durasi (Hari)",
            "Tanggal Sewa",
            "Tanggal Kembali",
            "Status",
            "Pembayaran Sewa",
            "Pendapatan Sewa (Rp)",
            "Denda (Rp)",
            "Status Denda",
        ];
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;

        $denda = Denda::where("penyewaan_id", $row->id)->first();
        $nominalDenda = $denda->nominal ?? 0;
        $statusDenda  = $denda->status ?? "-";

        $peralatanList = [];
        $kodeList = [];
        $totalJumlah = 0;

        foreach ($row->detailPenyewaans as $d) {
            $peralatanList[] = $d->peralatan->nama_barang ?? "-";
            $kodeList[]      = $d->peralatan->kode_barang ?? "-";
            $totalJumlah    += $d->jumlah;
        }

        $durasi = $row->detailPenyewaans->first()->durasi_hari ?? 0;
        $pendapatan = in_array($row->payment_status, ["paid"]) ? $row->total_price : 0;

        return [
            $no,
            $row->kode_penyewaan,
            optional($row->created_at)->format("d/m/Y H:i"),
            $row->customer_name,
            $row->customer_id_card,
            implode(", ", $peralatanList),
            implode(", ", $kodeList),
            $totalJumlah,
            $durasi,
            \Carbon\Carbon::parse($row->pickup_date)->format("d/m/Y"),
            \Carbon\Carbon::parse($row->return_date)->format("d/m/Y"),
            strtoupper($row->status),
            strtoupper($row->payment_status),
            $pendapatan,
            $nominalDenda,
            strtoupper($statusDenda),
        ];
    }

    public function styles(Worksheet $sheet): array   // ← tambahkan tipe
    {
        return [
            1 => [
                "font" => ["bold" => true, "color" => ["rgb" => "FFFFFF"]],
                "fill" => [
                    "fillType"   => "solid",
                    "startColor" => ["rgb" => "3B82F6"],
                ],
                "alignment" => ["horizontal" => "center", "vertical" => "center"],
            ],
        ];
    }

    public function title(): string
    {
        return "Laporan Penyewaan";
    }
}
