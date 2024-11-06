<?php

require_once __DIR__ . '/../config/database.php';
$db = new Database();

// Get the userId from the request (e.g., passed as a query parameter)
$userId = $_GET['userId'] ?? '';
if (!preg_match('/^[a-f\d]{24}$/i', $userId)) {
    echo json_encode(['error' => 'Invalid User ID format.']);
    exit;
}

// Define the collection name for carts
$cartCollection = $db->getCollection("Carts");

// Find the cart document for the specified userId
$cart = $cartCollection->findOne(['userId' => new MongoDB\BSON\ObjectId($userId)]);

if ($cart) {
    // Convert product ObjectId to string for easy handling in JavaScript
    $cartData = [
        '_id' => (string)$cart['_id'],
        'userId' => (string)$cart['userId'],
        'products' => array_map(function ($product) {
            return [
                'productId' => (string)$product['productId'],
                'quantity' => $product['quantity']
            ];
        }, $cart['products']->getArrayCopy()), // Convert BSONArray to PHP array
        'createdAt' => $cart['createdAt']->toDateTime()->format('Y-m-d H:i:s'),
        'updatedAt' => $cart['updatedAt']->toDateTime()->format('Y-m-d H:i:s')
    ];

    echo json_encode($cartData);
} else {
    echo json_encode(['error' => 'Cart not found.']);
}
