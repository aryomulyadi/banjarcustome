<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('target_data', function (Blueprint $table) {
            $table->id();
            $table->string('customer', 160);
            $table->string('jenis', 80)->nullable();
            $table->date('waktu_pengambilan')->nullable();
            $table->string('type', 80)->nullable();
            $table->string('bahan', 80)->nullable();
            $table->string('warna', 80)->nullable();
            $table->string('pesanan_bahan', 8)->default('NOK');
            $table->string('progres_produksi', 8)->default('NOK');
            $table->string('finish', 8)->default('NOK');
            $table->unsignedInteger('total')->default(0);
            $table->timestamps();

            $table->index('jenis');
            $table->index('waktu_pengambilan');
            $table->index('pesanan_bahan');
            $table->index('progres_produksi');
            $table->index('finish');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('target_data');
    }
};
