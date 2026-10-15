<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Denda extends Model
{
    protected $table = "dendas";

    protected $fillable = [
        "kode_denda",
        "pengembalian_id",
        "penyewaan_id",
        "user_id",
        "petugas_id",
        "nominal",
        "alasan_denda",
        "status",
        "bukti_dibayar",
    ];

    public function pengembalian() {
        return $this->belongsTo(Pengembalian::class);
    }

    public function penyewaan() {
        return $this->belongsTo(Penyewaan::class);
    }

    public function petugas() {
        return $this->belongsTo(User::class, "petugas_id");
    }


    public function user() {
        return $this->belongsTo(User::class);
    }
}
