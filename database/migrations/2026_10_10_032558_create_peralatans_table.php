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
        Schema::create('peralatans', function (Blueprint $table) {
            $table->id();
            $table->foreignId("category_id")->constrained("categories")->onDelete("cascade");
            $table->string("kode_barang")->unique();
            $table->string("nama_barang");
            $table->text("deskripsi")->nullable();
            $table->decimal("price_per_day", 12, 2);
            $table->integer("stok_total")->default(0);
            $table->integer("stok_tersedia")->default(0);
            $table->decimal("price_denda_perhari", 12, 2);
            $table->enum("status", ["aktif", "nonaktif"])->default("nonaktif");
            $table->string("gambar_peralatan")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peralatans');
    }
};
