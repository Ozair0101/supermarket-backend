<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::all();
        
        if ($categories->count() == 0) {
            $this->call(CategorySeeder::class);
            $categories = Category::all();
        }
        
        // Fruits
        Product::create([
            'category_id' => $categories->where('slug', 'fruits')->first()->id,
            'name' => 'Organic Apples',
            'sku' => 'FRU-001',
            'description' => 'Fresh organic apples from local farms',
            'reorder_threshold' => 20,
            'track_expiry' => false
        ]);

        Product::create([
            'category_id' => $categories->where('slug', 'fruits')->first()->id,
            'name' => 'Bananas',
            'sku' => 'FRU-002',
            'description' => 'Fresh bananas',
            'reorder_threshold' => 50,
            'track_expiry' => false
        ]);

        // Vegetables
        Product::create([
            'category_id' => $categories->where('slug', 'vegetables')->first()->id,
            'name' => 'Carrots',
            'sku' => 'VEG-001',
            'description' => 'Fresh organic carrots',
            'reorder_threshold' => 30,
            'track_expiry' => false
        ]);

        // Dairy
        Product::create([
            'category_id' => $categories->where('slug', 'dairy')->first()->id,
            'name' => 'Whole Milk',
            'sku' => 'DAI-001',
            'description' => '1L whole milk',
            'reorder_threshold' => 10,
            'track_expiry' => true
        ]);

        // Bakery
        Product::create([
            'category_id' => $categories->where('slug', 'bakery')->first()->id,
            'name' => 'Whole Wheat Bread',
            'sku' => 'BAK-001',
            'description' => 'Fresh whole wheat bread',
            'reorder_threshold' => 5,
            'track_expiry' => true
        ]);

        // Beverages
        Product::create([
            'category_id' => $categories->where('slug', 'beverages')->first()->id,
            'name' => 'Mineral Water',
            'sku' => 'BEV-001',
            'description' => '500ml mineral water',
            'reorder_threshold' => 40,
            'track_expiry' => false
        ]);
    }
}