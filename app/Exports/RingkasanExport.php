<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RingkasanExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithTitle
{
    protected $summary;

    public function __construct(array $summary)
    {
        $this->summary = $summary;
    }

    public function array(): array
    {
        return [
            ["Total Booking",              $this->summary["total_booking"]],
            ["Total Booking Selesai",      $this->summary["total_completed"]],
            ["Total Booking Dibatalkan",   $this->summary["total_cancelled"]],
            ["Total Booking Ada Denda",    $this->summary["total_denda"]],
            ["", ""],
            ["Total Pendapatan Sewa (Rp)", $this->summary["total_pendapatan"]],
            ["Total Denda (Rp)",           $this->summary["total_nominal_denda"]],
            ["Total Pendapatan Bersih",    $this->summary["total_bersih"]],
            ["", ""],
            ["Periode",                    $this->summary["periode"]],
            ["Dicetak Pada",               now()->translatedFormat("d F Y H:i")],
        ];
    }

    public function headings(): array
    {
        return ["Keterangan", "Nilai"];
    }

    public function styles(Worksheet $sheet): array   // ← tambahkan tipe
    {
        return [
            1 => [
                "font" => ["bold" => true, "color" => ["rgb" => "FFFFFF"]],
                "fill" => [
                    "fillType"   => "solid",
                    "startColor" => ["rgb" => "10B981"],
                ],
            ],
            6 => ["font" => ["bold" => true]],
            7 => ["font" => ["bold" => true]],
            8 => ["font" => ["bold" => true, "color" => ["rgb" => "10B981"]]],
        ];
    }

    public function title(): string
    {
        return "Ringkasan";
    }
}
