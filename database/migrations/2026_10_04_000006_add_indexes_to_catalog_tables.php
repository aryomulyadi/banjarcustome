<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->index('category_id');
            $table->index('slug');
        });

        Schema::table('product_colors', function (Blueprint $table) {
            $table->index('product_id');
            $table->index('name');
        });

        Schema::table('galleries', function (Blueprint $table) {
            $table->index('category');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->index('slug');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['category_id']);
            $table->dropIndex(['slug']);
        });

        Schema::table('product_colors', function (Blueprint $table) {
            $table->dropIndex(['product_id']);
            $table->dropIndex(['name']);
        });

        Schema::table('galleries', function (Blueprint $table) {
            $table->dropIndex(['category']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['slug']);
        });
    }
};
