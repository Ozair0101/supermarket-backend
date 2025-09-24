<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('inventory_adjustments', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('user_id');
            $table->decimal('quantity_before', 10, 2)->default(0);
            $table->decimal('quantity_after', 10, 2)->default(0);
            $table->decimal('change', 10, 2)->default(0);
            $table->string('type'); // increase, decrease
            $table->text('reason');
            
            // Add foreign key constraints
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_adjustments', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropForeign(['user_id']);
            $table->dropColumn([
                'product_id',
                'user_id',
                'quantity_before',
                'quantity_after',
                'change',
                'type',
                'reason'
            ]);
        });
    }
};