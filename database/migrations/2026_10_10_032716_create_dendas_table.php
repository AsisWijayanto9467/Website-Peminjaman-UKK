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
        Schema::create('dendas', function (Blueprint $table) {
            $table->id();
            $table->string("kode_denda");
            $table->foreignId("penggambalian_id")->constrained("pengembalians")->onDelete("cascade");
            $table->foreignId("user_id")->constrained("users")->onDelete("cascade");
            $table->foreignId("petugas_id")->nullable()->constrained("users")->onDelete("set null");
            $table->decimal("nominal")->default(0);
            $table->text("alasan_denda")->nullable();
            $table->enum("status", ["belum dibayar", "sudah dibayar"])->default("sudah dibayar");
            $table->string("bukti_dibayar")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dendas');
    }
};
