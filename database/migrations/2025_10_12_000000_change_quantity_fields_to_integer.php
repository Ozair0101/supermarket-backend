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
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->integer('quantity')->default(0)->change();
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->integer('quantity')->default(0)->change();
        });

        Schema::table('inventory_adjustments', function (Blueprint $table) {
            $table->integer('quantity_before')->default(0)->change();
            $table->integer('quantity_after')->default(0)->change();
            $table->integer('change')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->decimal('quantity', 10, 2)->default(0)->change();
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->decimal('quantity', 10, 2)->default(0)->change();
        });

        Schema::table('inventory_adjustments', function (Blueprint $table) {
            $table->decimal('quantity_before', 10, 2)->default(0)->change();
            $table->decimal('quantity_after', 10, 2)->default(0)->change();
            $table->decimal('change', 10, 2)->default(0)->change();
        });
    }
};
