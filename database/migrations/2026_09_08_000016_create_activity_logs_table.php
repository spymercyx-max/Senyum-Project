<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->string('entity')->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->json('metadata')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamps();

            $table->index('actor_id');
            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
