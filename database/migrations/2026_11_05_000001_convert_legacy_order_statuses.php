<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Konversi status lama (ready/production) ke tahap ceklis per jenis
     * produk setelah UI status diubah jadi ceklis bebas:
     * - ready   → tahap produksi pertama: layout (jersey) / material_prep (kaos & lainnya)
     * - production → tahap aktif: printing (jersey) / cetak (kaos & lainnya)
     */
    public function up(): void
    {
        DB::table('custom_orders')
            ->where('status', 'ready')
            ->update([
                'status' => DB::raw("CASE WHEN LOWER(COALESCE(service_type, '')) LIKE '%jersey%' THEN 'layout' ELSE 'material_prep' END"),
            ]);

        DB::table('custom_orders')
            ->where('status', 'production')
            ->update([
                'status' => DB::raw("CASE WHEN LOWER(COALESCE(service_type, '')) LIKE '%jersey%' THEN 'printing' ELSE 'cetak' END"),
            ]);
    }

    public function down(): void
    {
        DB::table('custom_orders')
            ->whereIn('status', ['layout', 'material_prep'])
            ->update(['status' => 'ready']);

        DB::table('custom_orders')
            ->whereIn('status', ['printing', 'cetak'])
            ->update(['status' => 'production']);
    }
};
