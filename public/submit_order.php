<?php
require_once __DIR__ . '/../config/database.php';
$db = new Database();

// Read the JSON input
$data = json_decode(file_get_contents('php://input'), true);

// Extract order details from the JSON data
$userId = $data['orderDetails']['userId'];
$products = $data['orderDetails']['products'];
$totalAmount = (int)$data['orderDetails']['totalAmount'];
$orderStatus = $data['orderDetails']['orderStatus'];
$orderDate = new MongoDB\BSON\UTCDateTime(new DateTime($data['orderDetails']['orderDate']));
$deliveryDate = $data['orderDetails']['deliveryDate'] ? new MongoDB\BSON\UTCDateTime(new DateTime($data['orderDetails']['deliveryDate'])) : null;

// Extract shipping address
$shippingAddress = [
    'street' => $data['shippingAddress']['street'],
    'city' => $data['shippingAddress']['city'],
    'state' => $data['shippingAddress']['state'],
    'postalCode' => $data['shippingAddress']['postalCode']
];

// Extract payment details
$payment = [
    'amount' => $data['payment']['amount'],
    'paymentMethod' => $data['payment']['paymentMethod'],
    'paymentDate' => new MongoDB\BSON\UTCDateTime(new DateTime($data['payment']['paymentDate'])),
    'paymentStatus' => $data['payment']['paymentStatus'],
    'historyDate' => $data['payment']['historyDate']
];

// Define collections
$orderCollection = $db->getCollection("Transactions"); // Correct collection name "Transactions"
$cartCollection = $db->getCollection("Carts"); // Collection name for the cart

// Check if a transaction with the same userId already exists
$existingTransaction = $orderCollection->findOne(['userId' => new MongoDB\BSON\ObjectId($userId)]);

if ($existingTransaction) {
    // If a transaction with the same userId is found, return an error
    echo json_encode(['success' => false, 'error' => 'A transaction for this user already exists.']);
    exit;
}

// Prepare the order document
$order = [
    'userId' => new MongoDB\BSON\ObjectId($userId),
    'products' => array_map(function ($product) {
        return [
            'productId' => new MongoDB\BSON\ObjectId($product['productId']),
            'quantity' => (int)$product['quantity'],
            'price' => (float)$product['price']
        ];
    }, $products),
    'totalAmount' => (float)$totalAmount,
    'orderStatus' => "delivered",
    'orderDate' => $orderDate,
    'deliveryDate' => $deliveryDate,
    'shippingAddress' => $shippingAddress,
    'payment' => $payment
];

try {
    // Insert the order
    $orderCollection->insertOne($order);

    // Clear the user's cart products after successful order placement
    $cartCollection->updateOne(
        ['userId' => new MongoDB\BSON\ObjectId($userId)], // Find the cart by userId
        ['$set' => ['products' => []]] // Set products to an empty array
    );

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Failed to place order: ' . $e->getMessage()]);
}
?>
