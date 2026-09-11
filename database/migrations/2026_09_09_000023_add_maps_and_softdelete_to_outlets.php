<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('outlets', function (Blueprint $table) {
            $table->text('google_maps_url')->nullable()->after('notes');
            // location_source: google_maps | manual_correction | legacy_import
            $table->string('location_source', 30)->nullable()->after('google_maps_url');
            $table->timestamp('location_verified_at')->nullable()->after('location_source');
            $table->softDeletes()->after('location_verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('outlets', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['google_maps_url', 'location_source', 'location_verified_at']);
        });
    }
};
