<?php
require_once __DIR__ . '/../config/database.php';
$db = new Database();

// Get JSON input
$data = json_decode(file_get_contents('php://input'), true);
$userId = $data['userId'];
$productId = $data['productId'];
$quantity = $data['quantity'] ?? 1; // Default to 1 if not provided

// Define the collection name for carts
$cartCollection = $db->getCollection("Carts");

// Check if a cart for this user already exists
$cart = $cartCollection->findOne(['userId' => new MongoDB\BSON\ObjectId($userId)]);

if ($cart) {
    // Cart exists, check if the product is already in the cart
    $productExists = false;
    foreach ($cart['products'] as &$product) {
        if ($product['productId'] == new MongoDB\BSON\ObjectId($productId)) {
            $product['quantity'] += $quantity; // Increase the quantity
            $productExists = true;
            break;
        }
    }

    if (!$productExists) {
        // If the product is not in the cart, add it
        $cart['products'][] = [
            'productId' => new MongoDB\BSON\ObjectId($productId),
            'quantity' => $quantity
        ];
    }

    // Update the cart with the new product array
    $cartCollection->updateOne(
        ['_id' => $cart['_id']],
        [
            '$set' => [
                'products' => $cart['products'],
                'updatedAt' => new MongoDB\BSON\UTCDateTime()
            ]
        ]
    );
} else {
    // Cart does not exist, create a new one
    $cartCollection->insertOne([
        'userId' => new MongoDB\BSON\ObjectId($userId),
        'products' => [
            [
                'productId' => new MongoDB\BSON\ObjectId($productId),
                'quantity' => $quantity
            ]
        ],
        'createdAt' => new MongoDB\BSON\UTCDateTime(),
        'updatedAt' => new MongoDB\BSON\UTCDateTime()
    ]);
}

// Return success response
echo json_encode(['success' => true]);
?>
