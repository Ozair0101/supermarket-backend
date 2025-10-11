<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;

class PurchaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = Supplier::all();
        $users = User::all();
        
        if ($suppliers->count() == 0) {
            $this->call(SupplierSeeder::class);
            $suppliers = Supplier::all();
        }
        
        if ($users->count() == 0) {
            $this->call(UserSeeder::class);
            $users = User::all();
        }
        
        // Create a sample purchase
        Purchase::create([
            'supplier_id' => $suppliers->first()->id,
            'created_by' => $users->first()->id,
            'invoice_number' => 'INV-001',
            'sub_total' => 540.00,
            'discount' => 0,
            'tax' => 0,
            'total' => 540.00,
            'paid' => 540.00,
            'remaining' => 0,
            'status' => 'paid',
            'purchase_date' => now()->format('Y-m-d'),
            'branch_id' => 1,
        ]);
    }
}