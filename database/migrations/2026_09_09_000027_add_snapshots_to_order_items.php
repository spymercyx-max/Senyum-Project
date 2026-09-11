<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->string('product_name')->nullable()->after('product_id');
            $table->string('sku')->nullable()->after('product_name');
        });

        Schema::table('transaction_items', function (Blueprint $table) {
            $table->string('product_name')->nullable()->after('product_id');
            $table->string('sku')->nullable()->after('product_name');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropColumn(['product_name', 'sku']);
        });
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->dropColumn(['product_name', 'sku']);
        });
    }
};
