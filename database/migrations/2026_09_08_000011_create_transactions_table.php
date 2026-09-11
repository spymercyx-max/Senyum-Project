<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('distributor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('outlet_id')->constrained('outlets')->cascadeOnDelete();
            $table->string('status')->default('completed');
            $table->unsignedBigInteger('total_amount')->default(0);
            $table->text('notes')->nullable();
            $table->timestamp('sold_at')->nullable();
            $table->timestamps();

            $table->index('distributor_id');
            $table->index('outlet_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
