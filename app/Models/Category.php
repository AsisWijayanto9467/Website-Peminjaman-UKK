<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = "categories";

    protected $fillable = [
        "nama_kategori",
        "deskripsi_kategori",
        "status"
    ];

    public function peralatans() {
        return $this->hasMany(Peralatan::class);
    }
}
