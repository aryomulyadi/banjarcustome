<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('options', function (Blueprint $table) {
            $table->unsignedInteger('fee')->default(0);
            $table->string('image')->nullable();
            $table->boolean('active')->default(true);
            $table->unsignedInteger('sort')->default(0);

            $table->index(['kind', 'active', 'sort']);
        });
    }

    public function down(): void
    {
        Schema::table('options', function (Blueprint $table) {
            $table->dropIndex(['kind', 'active', 'sort']);
            $table->dropColumn(['fee', 'image', 'active', 'sort']);
        });
    }
};
