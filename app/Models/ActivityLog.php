<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $table = "activity_logs";

    protected $fillable = [
        "user_id",
        "activity",
        "status",
        "ip_address",
        "user_agent"
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
