<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('po_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->string('from', 30)->nullable();
            $table->string('to', 30);
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index('purchase_order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('po_status_histories');
    }
};
