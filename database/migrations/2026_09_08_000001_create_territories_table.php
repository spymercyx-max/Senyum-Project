<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('territories', function (Blueprint $table) {
            $table->id();
            $table->string('city');
            $table->string('district');
            $table->string('city_normalized');
            $table->string('district_normalized');
            $table->string('code')->nullable()->unique();
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['city_normalized', 'district_normalized']);
            $table->index('city');
            $table->index('district');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('territories');
    }
};
