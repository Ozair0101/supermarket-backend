<?php
require_once 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

// Load .env file
$env = parse_ini_file('.env');

// Configure database connection
$capsule = new Capsule;
$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => $env['DB_HOST'],
    'database'  => $env['DB_DATABASE'],
    'username'  => $env['DB_USERNAME'],
    'password'  => $env['DB_PASSWORD'],
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

try {
    // Check if we can connect to the database
    $capsule->getConnection()->getPdo();
    echo "Database connection successful!\n";
    
    // Check if products table exists and its structure
    if ($capsule->schema()->hasTable('products')) {
        echo "Products table exists\n";
        $columns = $capsule->schema()->getColumnListing('products');
        echo "Products table columns: " . implode(', ', $columns) . "\n";
    } else {
        echo "Products table does not exist\n";
    }
    
    // Check if purchase_items table exists and its structure
    if ($capsule->schema()->hasTable('purchase_items')) {
        echo "Purchase items table exists\n";
        $columns = $capsule->schema()->getColumnListing('purchase_items');
        echo "Purchase items table columns: " . implode(', ', $columns) . "\n";
    } else {
        echo "Purchase items table does not exist\n";
    }
    
    // Check migrations table
    if ($capsule->schema()->hasTable('migrations')) {
        echo "Migrations table exists\n";
        $migrations = $capsule->table('migrations')->get();
        echo "Applied migrations:\n";
        foreach ($migrations as $migration) {
            echo "- " . $migration->migration . " (batch " . $migration->batch . ")\n";
        }
    } else {
        echo "Migrations table does not exist\n";
    }
} catch (Exception $e) {
    echo "Database connection failed: " . $e->getMessage() . "\n";
}
?>