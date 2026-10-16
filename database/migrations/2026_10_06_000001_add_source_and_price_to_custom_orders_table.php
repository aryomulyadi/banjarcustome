<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('custom_orders', function (Blueprint $table) {
            $table->string('source', 10)->default('form');
            $table->unsignedBigInteger('unit_price')->nullable();
            $table->unsignedBigInteger('total_price')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('custom_orders', function (Blueprint $table) {
            $table->dropColumn(['source', 'unit_price', 'total_price']);
        });
    }
};
