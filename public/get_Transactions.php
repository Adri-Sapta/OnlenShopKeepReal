<?php
require_once __DIR__ . '/../config/database.php';
$db = new Database();

// Get the userId from the query parameter
$userId = $_GET['userId'] ?? '';
if (!preg_match('/^[a-f\d]{24}$/i', $userId)) {
    echo json_encode(['error' => 'Invalid User ID format.']);
    exit;
}

// Define the collection for transactions
$transactionCollection = $db->getCollection("Transactions");
$productCollection = $db->getCollection("Products");

try {
    // Find transactions by userId
    $transactions = $transactionCollection->find(['userId' => new MongoDB\BSON\ObjectId($userId)]);
    $transactionData = [];

    foreach ($transactions as $transaction) {
        $products = array_map(function ($product) use ($productCollection) {
            $productDetails = $productCollection->findOne(['_id' => new MongoDB\BSON\ObjectId($product['productId'])]);
            return [
                'productId' => (string)$product['productId'],
                'name' => $productDetails['name'],
                'quantity' => $product['quantity'],
                'price' => $product['price'],
                'images' => isset($productDetails['images']) ? $productDetails['images'] : [] // Retrieve images if available
                
            ];
        }, $transaction['products']->getArrayCopy());

        $transactionData[] = [
            '_id' => (string)$transaction['_id'],
            'products' => $products,
            'totalAmount' => $transaction['totalAmount'],
            'orderStatus' => $transaction['orderStatus'],
            'orderDate' => $transaction['orderDate']->toDateTime()->format('Y-m-d H:i:s'),
            'deliveryDate' => $transaction['deliveryDate'] ? $transaction['deliveryDate']->toDateTime()->format('Y-m-d H:i:s') : null,
            'shippingAddress' => $transaction['shippingAddress'],
            'payment' => [
                'amount' => $transaction['payment']['amount'],
                'paymentMethod' => $transaction['payment']['paymentMethod'],
                'paymentDate' => $transaction['payment']['paymentDate']->toDateTime()->format('Y-m-d H:i:s')
            ]
        ];
    }

    echo json_encode($transactionData);

} catch (Exception $e) {
    echo json_encode(['error' => 'An error occurred: ' . $e->getMessage()]);
}
?>
