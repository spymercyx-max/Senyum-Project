<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            // fulfillment: delivery (DIKIRIM) | pickup (DIAMBIL)
            $table->string('fulfillment', 20)->default('delivery')->after('status');
            $table->date('order_date')->nullable()->after('fulfillment');
            $table->string('payment_proof')->nullable()->after('order_date');
            $table->string('tracking_number')->nullable()->after('payment_proof');
            $table->text('delivery_note')->nullable()->after('tracking_number');
            $table->text('pickup_message')->nullable()->after('delivery_note');
            $table->text('rejection_reason')->nullable()->after('pickup_message');

            $table->index('fulfillment');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropIndex(['fulfillment']);
            $table->dropColumn([
                'fulfillment', 'order_date', 'payment_proof', 'tracking_number',
                'delivery_note', 'pickup_message', 'rejection_reason',
            ]);
        });
    }
};
