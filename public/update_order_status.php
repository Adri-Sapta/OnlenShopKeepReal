<?php
require_once __DIR__ . '/../config/database.php';
$db = new Database();

// Read the JSON input
$data = json_decode(file_get_contents('php://input'), true);

// Extract order ID and new status
$orderId = $data['orderId'] ?? '';
$newStatus = $data['newStatus'] ?? '';

if (!preg_match('/^[a-f\d]{24}$/i', $orderId)) {
    echo json_encode(['error' => 'Invalid Order ID format.']);
    exit;
}

try {
    // Define the collections for transactions and purchase history
    $transactionCollection = $db->getCollection("Transactions");
    $purchaseHistoryCollection = $db->getCollection("PurchaseHistory");

    // Find the transaction data
    $transaction = $transactionCollection->findOne(['_id' => new MongoDB\BSON\ObjectId($orderId)]);

    if ($transaction) {
        // Update the order status in the transaction document
        $transaction['orderStatus'] = $newStatus;

        // Move the transaction data to the PurchaseHistory collection
        $purchaseHistoryCollection->insertOne([
            'userId' => $transaction['userId'],
            'transactionId' => $transaction['_id'],
            'products' => $transaction['products'],
            'totalAmount' => $transaction['totalAmount'],
            'orderStatus' => $transaction['orderStatus'],
            'orderDate' => $transaction['orderDate'],
            'deliveryDate' => $transaction['deliveryDate'],
            'shippingAddress' => $transaction['shippingAddress'],
            'payment' => $transaction['payment'],
            'historyDate' => new MongoDB\BSON\UTCDateTime(), // Set the current date and time as the history date
        ]);

        // Delete the transaction from the Transactions collection
        $transactionCollection->deleteOne(['_id' => new MongoDB\BSON\ObjectId($orderId)]);

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Transaction not found.']);
    }

} catch (Exception $e) {
    echo json_encode(['error' => 'An error occurred: ' . $e->getMessage()]);
}
?>
