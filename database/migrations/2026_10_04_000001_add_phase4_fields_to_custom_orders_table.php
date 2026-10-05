<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $tokens = [];

        DB::table('custom_orders')->orderBy('id')->pluck('id')->each(function ($id) use (&$tokens) {
            $tokens[$id] = Str::random(40);
        });

        DB::statement('ALTER TABLE custom_orders RENAME TO custom_orders_old');

        Schema::create('custom_orders', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_token', 40);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('whatsapp_number');
            $table->unsignedInteger('quantity')->nullable();
            $table->date('deadline')->nullable();
            $table->string('service_type', 60)->nullable();
            $table->string('delivery_method', 20)->nullable();
            $table->text('address')->nullable();
            $table->json('size_quantities')->nullable();
            $table->string('notes', 1000)->nullable();
            $table->text('order_details')->nullable();
            $table->string('design_file')->nullable();
            $table->string('status', 20)->default('pending');
            $table->timestamps();

            $table->unique('tracking_token');
            $table->index('status');
            $table->index('created_at');
            $table->index('whatsapp_number');
            $table->index('name');
        });

        $columns = '
            id, tracking_token, user_id, product_id, name, whatsapp_number, quantity,
            deadline, service_type, delivery_method, address, size_quantities, notes,
            order_details, design_file, status, created_at, updated_at';

        $select = '
            id, ?, user_id, product_id, name, whatsapp_number, quantity,
            NULL, NULL, NULL, NULL, NULL, NULL,
            order_details, design_file, status, created_at, updated_at';

        foreach ($tokens as $id => $token) {
            DB::statement(
                "INSERT INTO custom_orders ({$columns})
                 SELECT {$select} FROM custom_orders_old WHERE id = ?",
                [$token, $id]
            );
        }

        DB::statement('DROP TABLE custom_orders_old');
    }

    public function down(): void
    {
        Schema::table('custom_orders', function (Blueprint $table) {
            $table->dropIndex(['custom_orders_tracking_token_unique']);
            $table->dropIndex(['custom_orders_status_index']);
            $table->dropIndex(['custom_orders_created_at_index']);
            $table->dropIndex(['custom_orders_whatsapp_number_index']);
            $table->dropIndex(['custom_orders_name_index']);
            $table->dropColumn([
                'tracking_token',
                'deadline',
                'service_type',
                'delivery_method',
                'address',
                'size_quantities',
                'notes',
            ]);
        });
    }
};
