<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('buyer_name')->nullable()->after('outlet_id');
            // type: outlet | retail (jual ecer)
            $table->string('type', 20)->default('outlet')->after('status');
            $table->softDeletes()->after('sold_at');

            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['type']);
            $table->dropSoftDeletes();
            $table->dropColumn(['buyer_name', 'type']);
        });
    }
};
