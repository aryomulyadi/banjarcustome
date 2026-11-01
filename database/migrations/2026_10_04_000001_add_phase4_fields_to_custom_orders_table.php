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
        if (! Schema::hasTable('custom_orders') && Schema::hasTable('custom_orders_old')) {
            DB::statement('ALTER TABLE custom_orders_old RENAME TO custom_orders');
        }

        if (Schema::hasTable('custom_orders') && Schema::hasTable('custom_orders_old')) {
            DB::statement('DROP TABLE custom_orders_old');
        }

        if (Schema::hasTable('custom_orders') && Schema::hasColumn('custom_orders', 'tracking_token')) {
            if (Schema::hasTable('custom_orders_old')) {
                if (! DB::table('custom_orders')->exists()) {
                    $this->copyRows(
                        DB::table('custom_orders_old')->orderBy('id')->pluck('id')->all()
                    );
                }

                DB::statement('DROP TABLE custom_orders_old');
            }

            $this->ensureForeignKeys();

            return;
        }

        $ids = DB::table('custom_orders')->orderBy('id')->pluck('id')->all();

        if ($this->isMySql()) {
            Schema::table('custom_orders', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropForeign(['product_id']);
            });
        }

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

        $this->copyRows($ids);

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

    private function copyRows(array $ids): void
    {
        $columns = '
            id, tracking_token, user_id, product_id, name, whatsapp_number, quantity,
            deadline, service_type, delivery_method, address, size_quantities, notes,
            order_details, design_file, status, created_at, updated_at';

        $select = '
            id, ?, user_id, product_id, name, whatsapp_number, quantity,
            NULL, NULL, NULL, NULL, NULL, NULL,
            order_details, design_file, status, created_at, updated_at';

        foreach ($ids as $id) {
            DB::statement(
                "INSERT INTO custom_orders ({$columns})
                 SELECT {$select} FROM custom_orders_old WHERE id = ?",
                [Str::random(40), $id]
            );
        }
    }

    private function ensureForeignKeys(): void
    {
        if (! $this->isMySql()) {
            return;
        }

        foreach (['user_id' => 'users', 'product_id' => 'products'] as $column => $references) {
            try {
                DB::statement(
                    "ALTER TABLE custom_orders ADD CONSTRAINT custom_orders_{$column}_foreign
                     FOREIGN KEY ({$column}) REFERENCES {$references} (id) ON DELETE SET NULL"
                );
            } catch (Throwable) {
            }
        }
    }

    private function isMySql(): bool
    {
        return in_array(DB::getDriverName(), ['mysql', 'mariadb'], true);
    }
};
