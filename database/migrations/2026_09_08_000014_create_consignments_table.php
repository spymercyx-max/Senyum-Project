<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distributor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('outlet_id')->constrained('outlets')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->unsignedInteger('qty');
            $table->unsignedInteger('sold_qty')->default(0);
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consignments');
    }
};
