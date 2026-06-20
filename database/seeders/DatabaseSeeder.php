<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ─── Create demo users for each role ────────────────────────────────
        $users = [
            ['name' => 'Admin User',           'email' => 'admin@erp.com',      'role' => 'admin'],
            ['name' => 'Managing Director',     'email' => 'director@erp.com',   'role' => 'managing_director'],
            ['name' => 'Inventory Officer',     'email' => 'inventory@erp.com',  'role' => 'inventory_officer'],
            ['name' => 'Production Manager',    'email' => 'production@erp.com', 'role' => 'production_manager'],
            ['name' => 'Purchasing Manager',    'email' => 'purchasing@erp.com', 'role' => 'purchasing_manager'],
            ['name' => 'Sales Officer',         'email' => 'sales@erp.com',      'role' => 'sales_officer'],
            ['name' => 'Accountant',            'email' => 'accountant@erp.com', 'role' => 'accountant'],
        ];

        foreach ($users as $userData) {
            User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => Hash::make('password123'),
                'role' => $userData['role'],
            ]);
        }

        // ─── Seed sample products ───────────────────────────────────────────
        $products = [
            ['product_name' => 'Steel Rod (10mm)',      'sku' => 'RM-STEEL-001', 'type' => 'raw_material',     'price' => 25.50,  'quantity' => 500,  'min_stock_level' => 50],
            ['product_name' => 'Copper Wire (2mm)',     'sku' => 'RM-COPPER-001','type' => 'raw_material',     'price' => 12.75,  'quantity' => 300,  'min_stock_level' => 30],
            ['product_name' => 'Plastic Granules (1kg)','sku' => 'RM-PLAST-001', 'type' => 'raw_material',     'price' => 8.00,   'quantity' => 1000, 'min_stock_level' => 100],
            ['product_name' => 'Aluminium Sheet (1m²)', 'sku' => 'RM-ALUM-001',  'type' => 'raw_material',     'price' => 45.00,  'quantity' => 3,    'min_stock_level' => 20],
            ['product_name' => 'Circuit Board Type-A',  'sku' => 'FP-CIRB-001',  'type' => 'finished_product', 'price' => 150.00, 'quantity' => 75,   'min_stock_level' => 10],
            ['product_name' => 'Motor Assembly Unit',   'sku' => 'FP-MOTR-001',  'type' => 'finished_product', 'price' => 320.00, 'quantity' => 2,    'min_stock_level' => 5],
            ['product_name' => 'LED Panel (60x60)',     'sku' => 'FP-LED-001',   'type' => 'finished_product', 'price' => 85.00,  'quantity' => 120,  'min_stock_level' => 15],
            ['product_name' => 'Power Supply 500W',     'sku' => 'FP-PSU-001',   'type' => 'finished_product', 'price' => 65.00,  'quantity' => 200,  'min_stock_level' => 25],
        ];

        foreach ($products as $productData) {
            Product::create($productData);
        }

        // ─── Seed sample suppliers ──────────────────────────────────────────
        $suppliers = [
            ['supplier_name' => 'Global Steel Corp',      'contact_person' => 'John Smith',     'phone' => '+94 77 123 4567', 'email' => 'john@globalsteel.com',     'address' => '45 Industrial Zone, Colombo 15'],
            ['supplier_name' => 'Pacific Electronics',     'contact_person' => 'Sarah Chen',     'phone' => '+94 76 234 5678', 'email' => 'sarah@pacificelec.com',    'address' => '12 Tech Park, Kaduwela'],
            ['supplier_name' => 'Lanka Plastics Ltd',      'contact_person' => 'Ruwan Perera',   'phone' => '+94 71 345 6789', 'email' => 'ruwan@lankaplastics.lk',   'address' => '78 Factory Road, Horana'],
            ['supplier_name' => 'Metro Metals Trading',    'contact_person' => 'Anil Fernando',  'phone' => '+94 70 456 7890', 'email' => 'anil@metrometals.com',     'address' => '23 Harbor Road, Colombo 01'],
        ];

        foreach ($suppliers as $supplierData) {
            Supplier::create($supplierData);
        }
    }
}
