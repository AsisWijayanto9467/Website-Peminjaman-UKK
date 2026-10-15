<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penyewaan extends Model
{
    protected $table = "penyewaans";

    protected $fillable = [
        "user_id",
        "petugas_id",
        "kode_penyewaan",
        "pickup_date",
        "return_date",
        "status",
        "payment_status",
        "total_price",
        "customer_name",
        "customer_id_card",
        "customer_address",
        "face_photo",
        "id_card_photo",
        "payment_photo"
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function petugas() {
        return $this->belongsTo(User::class, "petugas_id");
    }

    public function detailPenyewaans() {
        return $this->hasMany(DetailPenyewaan::class);
    }

    public function pengembalians() {
        return $this->hasMany(Pengembalian::class);
    }

    public function dendas() {
        return $this->hasMany(Denda::class);
    }
}
