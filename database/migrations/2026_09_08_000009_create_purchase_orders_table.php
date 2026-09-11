<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('distributor_id')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('draft');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('total_amount')->default(0);
            $table->timestamps();

            $table->index('distributor_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
