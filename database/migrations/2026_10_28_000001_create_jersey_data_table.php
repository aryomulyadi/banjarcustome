<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jersey_data', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal_masuk');
            $table->string('desainer', 60);
            $table->string('customer', 160);
            $table->date('deadline')->nullable();
            $table->string('jenis_kerah', 80)->nullable();
            $table->string('jenis_bahan', 80)->nullable();
            $table->string('model', 80)->nullable();
            $table->string('status', 40)->default('Proses Desain');
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index('desainer');
            $table->index('status');
            $table->index('deadline');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jersey_data');
    }
};
