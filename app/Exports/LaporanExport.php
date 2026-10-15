<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class LaporanExport implements Export, WithMultipleSheets
{
    protected $penyewaans;
    protected $summary;
    protected $dateFrom;
    protected $dateTo;

    public function __construct($penyewaans, array $summary, $dateFrom, $dateTo)
    {
        $this->penyewaans = $penyewaans;
        $this->summary    = $summary;
        $this->dateFrom   = $dateFrom;
        $this->dateTo     = $dateTo;
    }

    public function sheets(): array
    {
        return [
            new RingkasanExport($this->summary),
            new PenyewaanExport($this->penyewaans, $this->dateFrom, $this->dateTo),
        ];
    }
}
