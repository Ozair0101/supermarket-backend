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
        Schema::table('payments', function (Blueprint $table) {
            $table->string('payable_type');
            $table->unsignedBigInteger('payable_id');
            $table->unsignedBigInteger('user_id');
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('method')->default('cash'); // cash, card, bank_transfer
            $table->string('reference')->nullable();
            $table->datetime('paid_at');
            $table->text('notes')->nullable();
            
            // Add foreign key constraint
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Add index for polymorphic relationship
            $table->index(['payable_type', 'payable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['payable_type', 'payable_id']);
            $table->dropColumn([
                'payable_type',
                'payable_id',
                'user_id',
                'amount',
                'method',
                'reference',
                'paid_at',
                'notes'
            ]);
        });
    }
};