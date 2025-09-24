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
        Schema::table('product_batches', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id');
            $table->string('batch_number');
            $table->date('expiry_date')->nullable();
            $table->decimal('quantity', 10, 2)->default(0);
            $table->decimal('cost_price', 10, 2)->default(0);
            
            // Add foreign key constraint
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_batches', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropColumn([
                'product_id',
                'batch_number',
                'expiry_date',
                'quantity',
                'cost_price'
            ]);
        });
    }
};