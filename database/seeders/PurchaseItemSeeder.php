<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PurchaseItem;
use App\Models\Product;
use App\Models\Purchase;

class PurchaseItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::all();
        $purchases = Purchase::all();
        
        if ($products->count() == 0) {
            $this->call(ProductSeeder::class);
            $products = Product::all();
        }
        
        if ($purchases->count() == 0) {
            $this->call(PurchaseSeeder::class);
            $purchases = Purchase::all();
        }
        
        $purchaseId = $purchases->first()->id;
        
        // Organic Apples
        $apples = $products->where('sku', 'FRU-001')->first();
        if ($apples) {
            PurchaseItem::create([
                'purchase_id' => $purchaseId,
                'product_id' => $apples->id,
                'barcode' => '123456789012',
                'quantity' => 100,
                'unit_cost' => 1.50,
                'selling_price' => 2.50,
                'discount' => 0,
                'line_total' => 150.00
            ]);
        }

        // Bananas
        $bananas = $products->where('sku', 'FRU-002')->first();
        if ($bananas) {
            PurchaseItem::create([
                'purchase_id' => $purchaseId,
                'product_id' => $bananas->id,
                'barcode' => '123456789013',
                'quantity' => 200,
                'unit_cost' => 0.30,
                'selling_price' => 0.50,
                'discount' => 0,
                'line_total' => 60.00
            ]);
        }

        // Carrots
        $carrots = $products->where('sku', 'VEG-001')->first();
        if ($carrots) {
            PurchaseItem::create([
                'purchase_id' => $purchaseId,
                'product_id' => $carrots->id,
                'barcode' => '123456789014',
                'quantity' => 150,
                'unit_cost' => 0.80,
                'selling_price' => 1.20,
                'discount' => 0,
                'line_total' => 120.00
            ]);
        }

        // Whole Milk
        $milk = $products->where('sku', 'DAI-001')->first();
        if ($milk) {
            PurchaseItem::create([
                'purchase_id' => $purchaseId,
                'product_id' => $milk->id,
                'barcode' => '123456789015',
                'quantity' => 50,
                'unit_cost' => 1.00,
                'selling_price' => 1.50,
                'discount' => 0,
                'line_total' => 50.00
            ]);
        }

        // Whole Wheat Bread
        $bread = $products->where('sku', 'BAK-001')->first();
        if ($bread) {
            PurchaseItem::create([
                'purchase_id' => $purchaseId,
                'product_id' => $bread->id,
                'barcode' => '123456789016',
                'quantity' => 30,
                'unit_cost' => 2.00,
                'selling_price' => 3.50,
                'discount' => 0,
                'line_total' => 60.00
            ]);
        }

        // Mineral Water
        $water = $products->where('sku', 'BEV-001')->first();
        if ($water) {
            PurchaseItem::create([
                'purchase_id' => $purchaseId,
                'product_id' => $water->id,
                'barcode' => '123456789017',
                'quantity' => 200,
                'unit_cost' => 0.50,
                'selling_price' => 1.00,
                'discount' => 0,
                'line_total' => 100.00
            ]);
        }
    }
}