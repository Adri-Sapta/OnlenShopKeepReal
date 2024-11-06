<?php
require_once __DIR__ . '/../config/database.php';
$db = new Database();

// Get the userId from the query parameter
$userId = $_GET['userId'] ?? '';
if (!preg_match('/^[a-f\d]{24}$/i', $userId)) {
    echo json_encode(['error' => 'Invalid User ID format.']);
    exit;
}

// Define the collections for purchase history and products
$purchaseHistoryCollection = $db->getCollection("PurchaseHistory");
$productCollection = $db->getCollection("Products");

try {
    // Find purchase history by userId
    $purchases = $purchaseHistoryCollection->find(['userId' => new MongoDB\BSON\ObjectId($userId)]);

    $purchaseData = [];

    foreach ($purchases as $purchase) {
        // Fetch detailed product information for each product in the purchase
        $products = array_map(function ($product) use ($productCollection) {
            // Find the product details in the Products collection
            $productDetails = $productCollection->findOne(['_id' => new MongoDB\BSON\ObjectId($product['productId'])]);

            return [
                'productId' => (string)$product['productId'],
                'quantity' => $product['quantity'],
                'price' => $product['price'],
                'images' => isset($productDetails['images']) ? $productDetails['images'] : [] // Retrieve images if available
                
            ];
        }, $purchase['products']->getArrayCopy());

        // Structure each purchase data entry
        $purchaseData[] = [
            '_id' => (string)$purchase['_id'],
            'transactionId' => (string)$purchase['transactionId'],
            'products' => $products,
            'totalAmount' => $purchase['totalAmount'],
            'orderStatus' => $purchase['orderStatus'],
            'orderDate' => $purchase['orderDate']->toDateTime()->format('Y-m-d H:i:s'),
            'deliveryDate' => $purchase['deliveryDate'] ? $purchase['deliveryDate']->toDateTime()->format('Y-m-d H:i:s') : null,
            'shippingAddress' => $purchase['shippingAddress'],
            'payment' => [
                'method' => $purchase['payment']['paymentMethod'],
                'paymentDate' => $purchase['payment']['paymentDate']->toDateTime()->format('Y-m-d H:i:s')
            ],
            'historyDate' => $purchase['historyDate']->toDateTime()->format('Y-m-d H:i:s')
        ];
    }

    // Return the JSON response
    echo json_encode($purchaseData);

} catch (Exception $e) {
    echo json_encode(['error' => 'An error occurred: ' . $e->getMessage()]);
}
?>
