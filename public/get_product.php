<?php
require_once __DIR__ . '/../config/database.php';
$db = new Database();

// Get the productId from the query parameter
$productId = $_GET['productId'] ?? '';
if (!preg_match('/^[a-f\d]{24}$/i', $productId)) {
    echo json_encode(['error' => 'Invalid Product ID format.']);
    exit;
}

// Define the collection for products
$productCollection = $db->getCollection("Products");

// Find the product by productId
$product = $productCollection->findOne(['_id' => new MongoDB\BSON\ObjectId($productId)]);

if ($product) {
    // Convert the MongoDB result to an associative array with the necessary fields
    $productData = [
        'productId' => (string)$product['_id'],
        'name' => $product['name'],
        'price' => $product['price'],
        'images' => $product['images'], // Assuming 'image' field contains the image URL
        'description' => $product['description'], // Optional, if you want to include a description
        'category' => $product['category'] 
    ];

    echo json_encode($productData);
} else {
    echo json_encode(['error' => 'Product not found.']);
}
