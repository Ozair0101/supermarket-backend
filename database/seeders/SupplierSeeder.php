<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Supplier;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Supplier::create([
            'name' => 'ABC Food Suppliers',
            'contact_person' => 'John Smith',
            'phone' => '123-456-7890',
            'email' => 'john@abcfoods.com',
            'address' => '123 Main St, City, State 12345',
            'notes' => 'Primary supplier for fresh produce',
            'remaining_balance' => 1500.00,
        ]);

        Supplier::create([
            'name' => 'XYZ Beverage Distributors',
            'contact_person' => 'Jane Doe',
            'phone' => '987-654-3210',
            'email' => 'jane@xyzbeverages.com',
            'address' => '456 Oak Ave, City, State 12345',
            'notes' => 'Supplier for drinks and beverages',
            'remaining_balance' => 2500.00,
        ]);
    }
}