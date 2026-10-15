<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Peralatan extends Model
{
    protected $table = "peralatans";

    protected $fillable = [
        "category_id",
        "kode_barang",
        "nama_barang",
        "deskripsi",
        "price_per_day",
        "stok_total",
        "stok_tersedia",
        "price_denda_perhari",
        "status",
        "gambar_peralatan"
    ];

    public function category() {
        return $this->belongsTo(Category::class);
    }

    public function keranjangs() {
        return $this->hasMany(Keranjang::class);
    }

    public function detailPenyewaans() {
        return $this->hasMany(DetailPenyewaan::class);
    }
}
