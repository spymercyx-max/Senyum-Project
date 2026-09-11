<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('short_description')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('price')->default(0);
            $table->string('sku')->unique();
            $table->string('image')->nullable();
            $table->string('status')->default('draft');
            $table->boolean('featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->text('ingredients')->nullable();
            $table->string('availability_note')->nullable();
            $table->timestamps();

            $table->index('slug');
            $table->index('featured');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
