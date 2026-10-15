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
        Schema::create('penyewaans', function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->constrained("users")->onDelete("cascade");
            $table->foreignId("petugas_id")->nullable()->constrained("users")->onDelete("set null");
            $table->string("kode_penyewaan")->unique();
            $table->date("pickup_date");
            $table->date("return_date");
            $table->enum("status", ["padding", "cancelled", "approved", "declined", "rented", "checking", "denda", "completed"])->default("padding");
            $table->enum("payment_status", ["unpaid", "paid"])->default("unpaid");
            $table->decimal("total_price")->default(0);
            $table->string("customer_name");
            $table->text("customer_address");
            $table->string("customer_id_card");
            $table->string("face_photo")->nullable();
            $table->string("id_card_photo")->nullable();
            $table->string("payment_photo")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penyewaans');
    }
};
