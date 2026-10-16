<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('custom_orders', function (Blueprint $table) {
            $table->string('bahan', 120)->nullable()->after('service_type');
            $table->string('type', 120)->nullable()->after('bahan');
            $table->string('warna', 120)->nullable()->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('custom_orders', function (Blueprint $table) {
            $table->dropColumn(['bahan', 'type', 'warna']);
        });
    }
};
