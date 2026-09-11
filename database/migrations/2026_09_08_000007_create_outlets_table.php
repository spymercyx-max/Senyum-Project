<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outlets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('territory_id')->constrained('territories')->cascadeOnDelete();
            $table->foreignId('distributor_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->text('address');
            $table->string('city');
            $table->string('district');
            $table->string('phone')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('territory_id');
            $table->index('distributor_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outlets');
    }
};
