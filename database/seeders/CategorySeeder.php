<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::create([
            'name' => 'Fruits',
            'slug' => 'fruits',
            'description' => 'Fresh fruits from local and imported sources'
        ]);

        Category::create([
            'name' => 'Vegetables',
            'slug' => 'vegetables',
            'description' => 'Fresh vegetables and greens'
        ]);

        Category::create([
            'name' => 'Dairy',
            'slug' => 'dairy',
            'description' => 'Milk, cheese, yogurt and other dairy products'
        ]);

        Category::create([
            'name' => 'Bakery',
            'slug' => 'bakery',
            'description' => 'Fresh bread, pastries and baked goods'
        ]);

        Category::create([
            'name' => 'Beverages',
            'slug' => 'beverages',
            'description' => 'Soft drinks, juices, water and other beverages'
        ]);
    }
}