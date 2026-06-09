<?php

/**
 * Boots the test Symfony kernel, creates the SQLite database, and seeds fixtures.
 *
 * Usage: php Tests/App/setup.php
 */

require_once __DIR__.'/../../vendor/autoload.php';

use Dtc\GridBundle\Tests\App\Entity\Product;
use Dtc\GridBundle\Tests\App\Kernel;

// Clear previous cache/db
$cacheDir = sys_get_temp_dir().'/dtc_grid_test';
if (is_dir($cacheDir)) {
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($cacheDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($it as $item) {
        $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
    }
}

$kernel = new Kernel('test', true);
$kernel->boot();

$container = $kernel->getContainer();

/** @var Doctrine\ORM\EntityManagerInterface $em */
$em = $container->get('doctrine.orm.entity_manager');

// Create schema
$schemaTool = new Doctrine\ORM\Tools\SchemaTool($em);
$metadata = $em->getMetadataFactory()->getAllMetadata();
$schemaTool->createSchema($metadata);
echo "Database schema created.\n";

// Seed fixtures
$products = [
    ['name' => 'Wireless Keyboard', 'category' => 'Electronics', 'price' => 49.99, 'status' => 'In Stock'],
    ['name' => 'USB-C Hub', 'category' => 'Electronics', 'price' => 29.99, 'status' => 'In Stock'],
    ['name' => 'Standing Desk', 'category' => 'Furniture', 'price' => 399.00, 'status' => 'In Stock'],
    ['name' => 'Ergonomic Chair', 'category' => 'Furniture', 'price' => 549.00, 'status' => 'Backordered'],
    ['name' => 'Monitor Arm', 'category' => 'Accessories', 'price' => 89.99, 'status' => 'In Stock'],
    ['name' => 'Webcam HD', 'category' => 'Electronics', 'price' => 69.99, 'status' => 'In Stock'],
    ['name' => 'Desk Lamp', 'category' => 'Accessories', 'price' => 34.99, 'status' => 'Discontinued'],
    ['name' => 'Noise Cancelling Headphones', 'category' => 'Electronics', 'price' => 199.99, 'status' => 'In Stock'],
    ['name' => 'Cable Management Kit', 'category' => 'Accessories', 'price' => 14.99, 'status' => 'In Stock'],
    ['name' => 'Laptop Stand', 'category' => 'Accessories', 'price' => 44.99, 'status' => 'In Stock'],
];

foreach ($products as $data) {
    $product = new Product();
    $product->setName($data['name']);
    $product->setCategory($data['category']);
    $product->setPrice($data['price']);
    $product->setStatus($data['status']);
    $em->persist($product);
}
$em->flush();
echo 'Seeded '.count($products)." products.\n";

$kernel->shutdown();
echo "Setup complete.\n";
