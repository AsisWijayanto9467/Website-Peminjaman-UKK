<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;
    
    protected $table = "users";

    protected $fillable = [
        "name",
        "email",
        "password",
        "no_hp",
        "role",
        "photo_profile"
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function keranjangs() {
        return $this->hasMany(Keranjang::class);
    }

    public function penyewaans() {
        return $this->hasMant(Penyewaan::class);
    }
    public function petugasPenyewaan() {
        return $this->hasMany(Penyewaan::class, "petugas_id");
    }

    public function pengembalians() {
        return $this->hasMany(Pengembalian::class);
    }

    public function petugasPengembalian() {
        return $this->hasMany(Pengembalian::class, "petugas_id");
    }

    public function dendas() {
        return $this->hasMany(Denda::class);
    }

    public function petugasDenda() {
        return $this->hasMany(Denda::class, "petugas_id");
    }

    public function activityLog() {
        return $this->belongsTo(ActivityLog::class);
    }
}
