<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('custom_orders', function (Blueprint $table) {
            $table->boolean('pesan_bahan_ok')->default(false)->after('status');
            $table->boolean('produksi_ok')->default(false)->after('pesan_bahan_ok');
            $table->boolean('finish_ok')->default(false)->after('produksi_ok');
        });
    }

    public function down(): void
    {
        Schema::table('custom_orders', function (Blueprint $table) {
            $table->dropColumn(['pesan_bahan_ok', 'produksi_ok', 'finish_ok']);
        });
    }
};
