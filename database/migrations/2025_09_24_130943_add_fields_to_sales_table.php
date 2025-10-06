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
        Schema::table('sales', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('invoice_number')->nullable();
            $table->decimal('sub_total', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->decimal('paid', 10, 2)->default(0);
            $table->decimal('remaining', 10, 2)->default(0);
            $table->string('status')->default('pending'); // pending, completed, cancelled
            $table->string('payment_method')->default('cash'); // cash, card, bank_transfer
            $table->datetime('sale_date');
            $table->unsignedBigInteger('branch_id')->nullable();
            
            // Add foreign key constraints
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropForeign(['created_by']);
            $table->dropColumn([
                'customer_id',
                'created_by',
                'invoice_number',
                'sub_total',
                'discount',
                'tax',
                'total',
                'paid',
                'remaining',
                'status',
                'payment_method',
                'sale_date',
                'branch_id'
            ]);
        });
    }
};