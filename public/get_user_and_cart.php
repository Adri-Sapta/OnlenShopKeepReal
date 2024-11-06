<?php
require_once __DIR__ . '/../config/database.php';
$db = new Database();

// Get the userId from the query parameter
$userId = $_GET['userId'] ?? '';
if (!preg_match('/^[a-f\d]{24}$/i', $userId)) {
    echo json_encode(['error' => 'Invalid User ID format.']);
    exit;
}

// Define the collections for users and carts
$userCollection = $db->getCollection("Users");
$cartCollection = $db->getCollection("Carts");

try {
    // Fetch user details
    $user = $userCollection->findOne(['_id' => new MongoDB\BSON\ObjectId($userId)]);
    
    if (!$user) {
        echo json_encode(['error' => 'User not found.']);
        exit;
    }

    // Format user data
    $userData = [
        '_id' => (string)$user['_id'],
        'username' => $user['username'],
        'email' => $user['email'],
        'phone' => $user['phone'],
        'role' => $user['role'],
        'createdAt' => $user['createdAt']->toDateTime()->format('Y-m-d H:i:s'),
        'updatedAt' => $user['updatedAt']->toDateTime()->format('Y-m-d H:i:s')
    ];

    // Fetch cart details
    $cart = $cartCollection->findOne(['userId' => new MongoDB\BSON\ObjectId($userId)]);
    
    if (!$cart) {
        echo json_encode(['error' => 'Cart not found.']);
        exit;
    }

    // Format cart data
    $cartData = [
        '_id' => (string)$cart['_id'],
        'userId' => (string)$cart['userId'],
        'products' => array_map(function ($product) {
            return [
                'productId' => (string)$product['productId'],
                'quantity' => $product['quantity']
            ];
        }, $cart['products']->getArrayCopy()),
        'createdAt' => $cart['createdAt']->toDateTime()->format('Y-m-d H:i:s'),
        'updatedAt' => $cart['updatedAt']->toDateTime()->format('Y-m-d H:i:s')
    ];

    // Combine user and cart data into a single response
    $response = [
        'user' => $userData,
        'cart' => $cartData
    ];

    echo json_encode($response);
} catch (Exception $e) {
    echo json_encode(['error' => 'An error occurred: ' . $e->getMessage()]);
}
