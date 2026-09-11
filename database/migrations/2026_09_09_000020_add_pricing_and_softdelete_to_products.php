<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('distributor_price')->default(0)->after('price');
            $table->softDeletes()->after('sort_order');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn('distributor_price');
        });
    }
};
