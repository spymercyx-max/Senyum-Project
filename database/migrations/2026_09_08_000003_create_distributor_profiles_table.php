<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distributor_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->foreignId('territory_id')->nullable()->constrained('territories')->nullOnDelete();
            $table->string('status')->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('territory_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distributor_profiles');
    }
};
