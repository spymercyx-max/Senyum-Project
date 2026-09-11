<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Stok milik distributor per produk. Bertambah saat PO disetujui,
        // berkurang saat distributor mencatat penjualan. Tidak pernah
        // menyentuh stok pusat selain lewat approval PO.
        Schema::create('distributor_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distributor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedBigInteger('qty')->default(0);
            $table->timestamps();

            $table->unique(['distributor_id', 'product_id']);
            $table->index('distributor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distributor_stocks');
    }
};
