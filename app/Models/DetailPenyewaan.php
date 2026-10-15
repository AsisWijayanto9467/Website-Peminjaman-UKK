<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailPenyewaan extends Model
{
    protected $table = "detail_penyewaans";

    protected $fillable = [
        "penyewaan_id",
        "peralatan_id",
        "price_per_day",
        "subtotal",
        "durasi_hari",
        "jumlah"
    ];

    public function penyewaan() {
        return $this->belongsTo(Penyewaan::class);
    }

    public function peralatan() {
        return $this->belongsTo(Peralatan::class);
    }
}
