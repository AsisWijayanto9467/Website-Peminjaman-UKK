<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('detail_penyewaans', function (Blueprint $table) {
            $table->id();
            $table->foreignId("penyewaan_id")->constrained("penyewaans")->onDelete("cascade");
            $table->foreignId("peralatan_id")->constrained("peralatans")->onDelete("cascade");
            $table->decimal("price_per_day", 12,2 )->default(0);
            $table->decimal("subtotal", 12,2 )->default(0);
            $table->integer("durasi_hari")->default(0);
            $table->integer("jumlah")->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_penyewaans');
    }
};
