<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InformasiToko extends Model
{
    protected $table = "informasi_toko";

    protected $fillable = [
        "nama_toko",
        "kode_qr",
        "catatan_usaha",
    ];
}
