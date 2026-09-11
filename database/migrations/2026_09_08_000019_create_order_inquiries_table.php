<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_inquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('name')->nullable();
            $table->string('whatsapp')->nullable();
            $table->text('message')->nullable();
            $table->string('status')->default('new');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_inquiries');
    }
};
