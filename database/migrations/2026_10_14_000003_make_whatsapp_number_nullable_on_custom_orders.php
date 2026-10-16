<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * WhatsApp di kolom import bersifat opsional (file Excel pelacakan
     * produksi bisa tidak punya nomor). Form publik tetap mewajibkan
     * nomor lewat validasi request.
     */
    public function up(): void
    {
        Schema::table('custom_orders', function (Blueprint $table) {
            $table->string('whatsapp_number')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('custom_orders', function (Blueprint $table) {
            $table->string('whatsapp_number')->nullable(false)->change();
        });
    }
};
