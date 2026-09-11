<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Harga beli/acuan distributor saat transaksi dicatat (snapshot).
        // Harga jual ke outlet tetap di kolom price. Kolom baru, nullable,
        // tanpa mengubah data lama.
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->unsignedBigInteger('cost_price')->nullable()->after('price');
        });

        Schema::table('outlets', function (Blueprint $table) {
            $table->index(['latitude', 'longitude'], 'outlets_lat_lng_index');
        });
    }

    public function down(): void
    {
        Schema::table('outlets', function (Blueprint $table) {
            $table->dropIndex('outlets_lat_lng_index');
        });
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->dropColumn('cost_price');
        });
    }
};
