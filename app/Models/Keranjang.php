<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Keranjang extends Model
{
    protected $table = "keranjang";
    
    protected $fillable = [
        "user_id",
        "peralatan_id",
        "jumlah"
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function peralatan() {
        return $this->belongsTo(Peralatan::class);
    }
}
