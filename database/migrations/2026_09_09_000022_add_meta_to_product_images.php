<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->string('original_name')->nullable()->after('path');
            $table->unsignedBigInteger('size')->nullable()->after('original_name');
            $table->string('mime', 100)->nullable()->after('size');
        });
    }

    public function down(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->dropColumn(['original_name', 'size', 'mime']);
        });
    }
};
