<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengembalian extends Model
{
    protected $table = "pengembalians";

    protected $fillable = [
        "user_id",
        "penyewaan_id",
        "petugas_id",
        "actual_return_date",
        "status",
        "photo_prove",
        "catatan_pengguna",
        "catatan_petugas",
        "tanggal_pengajuan",
        "tanggal_konfirmasi",
    ];

    public function penyewaan() {
        return $this->belongsTo(Penyewaan::class);
    }

    public function petugas() {
        return $this->belongsTo(User::class, "petugas_id");
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function dendas() {
        return $this->hasMany(Denda::class);
    }
}
