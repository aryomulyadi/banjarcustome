<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('custom_orders', function (Blueprint $table) {
            $table->text('cancel_reason')->nullable()->after('status');
            $table->string('design_file_admin')->nullable()->after('design_file');
            $table->timestamp('design_approved_at')->nullable()->after('design_file_admin');
            $table->unsignedInteger('design_revisions')->default(0)->after('design_approved_at');
            $table->text('design_revision_reason')->nullable()->after('design_revisions');
            $table->unsignedBigInteger('dp_amount')->nullable()->after('total_price');
            $table->timestamp('dp_received_at')->nullable()->after('dp_amount');
            $table->string('assigned_operator', 120)->nullable()->after('dp_received_at');
            $table->string('spk_number', 40)->nullable()->after('assigned_operator');
            $table->timestamp('spk_issued_at')->nullable()->after('spk_number');
        });
    }

    public function down(): void
    {
        Schema::table('custom_orders', function (Blueprint $table) {
            $table->dropColumn([
                'cancel_reason', 'design_file_admin', 'design_approved_at',
                'design_revisions', 'design_revision_reason',
                'dp_amount', 'dp_received_at', 'assigned_operator',
                'spk_number', 'spk_issued_at',
            ]);
        });
    }
};
