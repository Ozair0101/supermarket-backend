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
        Schema::table('purchases', function (Blueprint $table) {
            $table->unsignedBigInteger('supplier_id');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('invoice_number');
            $table->decimal('sub_total', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->decimal('paid', 10, 2)->default(0);
            $table->decimal('remaining', 10, 2)->default(0);
            $table->string('status')->default('pending'); // pending, completed, cancelled
            $table->date('purchase_date');
            $table->unsignedBigInteger('branch_id')->nullable();
            
            // Add foreign key constraints
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropForeign(['created_by']);
            $table->dropColumn([
                'supplier_id',
                'created_by',
                'invoice_number',
                'sub_total',
                'discount',
                'tax',
                'total',
                'paid',
                'remaining',
                'status',
                'purchase_date',
                'branch_id'
            ]);
        });
    }
};