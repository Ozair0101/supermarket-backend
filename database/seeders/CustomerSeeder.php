<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Customer;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Customer::create([
            'name' => 'John Smith',
            'phone' => '123-456-7890',
            'email' => 'john.smith@email.com',
            'address' => '123 Main St, City, State 12345',
            'notes' => 'Regular customer',
            'remaining_balance' => 0.00
        ]);

        Customer::create([
            'name' => 'Jane Doe',
            'phone' => '987-654-3210',
            'email' => 'jane.doe@email.com',
            'address' => '456 Oak Ave, City, State 12345',
            'notes' => 'Prefers organic products',
            'remaining_balance' => 25.50
        ]);

        Customer::create([
            'name' => 'Bob Johnson',
            'phone' => '555-123-4567',
            'email' => 'bob.johnson@email.com',
            'address' => '789 Pine St, City, State 12345',
            'notes' => 'Business account',
            'remaining_balance' => 0.00
        ]);
    }
}