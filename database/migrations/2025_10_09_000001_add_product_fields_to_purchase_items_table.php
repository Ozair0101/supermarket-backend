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
            // Check if columns exist before adding them
            if (!Schema::hasColumn('purchase_items', 'barcode')) {
                $table->string('barcode')->nullable()->after('product_id');
            }
            
            if (!Schema::hasColumn('purchase_items', 'selling_price')) {
                $table->decimal('selling_price', 10, 2)->default(0)->after('unit_cost');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_items', 'barcode')) {
                $table->dropColumn('barcode');
            }
            
            if (Schema::hasColumn('purchase_items', 'selling_price')) {
                $table->dropColumn('selling_price');
            }
        });
    }
};