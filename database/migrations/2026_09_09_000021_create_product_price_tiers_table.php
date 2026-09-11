<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_price_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            // channel: customer | distributor
            $table->string('channel', 20)->default('customer');
            $table->unsignedInteger('min_qty');
            $table->unsignedInteger('max_qty')->nullable();
            $table->unsignedBigInteger('price');
            $table->timestamps();

            $table->index(['product_id', 'channel']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_price_tiers');
    }
};
