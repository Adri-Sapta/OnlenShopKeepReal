<?php
require_once __DIR__ . '/../config/database.php';
$db = new Database();

// Define collection names
$productCollection = "Products";
$collectionName = "Ratings";

// Retrieve and validate product ID
$productId = $_GET['id'] ?? '';
if (!preg_match('/^[a-f\d]{24}$/i', $productId)) {
    echo json_encode(['error' => 'Invalid Product ID format.']);
    exit;
}

// Define filters
$filter = ['_id' => new MongoDB\BSON\ObjectId($productId)];
$filter_rating = ['productId' => new MongoDB\BSON\ObjectId($productId)];

// Fetch data from the collections
$productData = $db->getData($productCollection, $filter)->toArray();
$ratingsData = $db->getData($collectionName, $filter_rating)->toArray();

if (!empty($productData)) {
    $product = $productData[0];
    $productArray = [
        'productId' => (string)$product['_id'],
        
        'name' => $product['name'],
        'price' => $product['price'],
        'images' => $product['images'],
        'description' => $product['description'],
        'ratings' => []
    ];

    // Populate the ratings array
    foreach ($ratingsData as $rating) {
        $filter_user = ['_id' => new MongoDB\BSON\ObjectId($rating['userId'])];
        $userData = $db->getData("Users", $filter_user)->toArray();
        $user = $userData[0];

        $productArray['ratings'][] = [
            'rating' => $rating['rating'],
            'comment' => $rating['comment'],
            'reviewDate' => $rating['reviewDate']->toDateTime()->format('d F Y'),
            'name' => $user['username']
        ];
    }

    // Return JSON response
    echo json_encode($productArray);
} else {
    echo json_encode(['error' => 'Product not found.']);
}
?>
