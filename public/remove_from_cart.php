<?php
require_once __DIR__ . '/../config/database.php';
$db = new Database();

// Get JSON input
$data = json_decode(file_get_contents('php://input'), true);
$userId = $data['userId'] ?? '';
$productId = $data['productId'] ?? '';

// Validate inputs
if (!preg_match('/^[a-f\d]{24}$/i', $userId) || !preg_match('/^[a-f\d]{24}$/i', $productId)) {
    echo json_encode(['error' => 'Invalid input data.']);
    exit;
}

// Define the collection for carts
$cartCollection = $db->getCollection("Carts");

try {
    // Remove the specified product from the user's cart
    $updateResult = $cartCollection->updateOne(
        ['userId' => new MongoDB\BSON\ObjectId($userId)],
        ['$pull' => ['products' => ['productId' => new MongoDB\BSON\ObjectId($productId)]]]
    );

    if ($updateResult->getModifiedCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Product removed from cart successfully.']);
    } else {
        echo json_encode(['error' => 'Cart or product not found.']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => 'An error occurred: ' . $e->getMessage()]);
}
