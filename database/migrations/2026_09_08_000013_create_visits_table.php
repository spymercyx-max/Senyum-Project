<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distributor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('outlet_id')->constrained('outlets')->cascadeOnDelete();
            $table->dateTime('visited_at');
            $table->string('status')->default('done');
            $table->text('notes')->nullable();
            $table->dateTime('follow_up_at')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamps();

            $table->index('outlet_id');
            $table->index('distributor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
