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
        Schema::create('pengembalians', function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->constrained("users")->onDelete("cascade");
            $table->foreignId("penyewaan_id")->constrained("penyewaans")->onDelete("cascade");
            $table->foreignId("petugas_id")->nullable()->constrained("users")->onDelete("set null");
            $table->date("actual_return_date");
            $table->enum("status", ["padding", "reject", "accept"])->default("padding");
            $table->string("photo_prove")->nullable();
            $table->text("catatan_pengguna")->nullable();
            $table->text("catatan_petugas")->nullable();
            $table->date("tanggal_pengajuan");
            $table->date("tanggal_konfirmasi");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengembalians');
    }
};
