<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outlet_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained('outlets')->cascadeOnDelete();
            $table->string('path');
            $table->string('disk')->default('public');
            $table->string('original_name')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->string('mime', 100)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('outlet_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outlet_images');
    }
};
