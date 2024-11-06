<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$db = new Database();
$collectionName = "Products";
$dataCursor = $db->getData($collectionName);

$productsData = [];
foreach ($dataCursor as $document) {
    $productsData[] = [
        'name' => $document['name'], 
        'images' => $document['images'],
        'price' => $document['price'],
        'category' => $document['category'],
        'id' => (string)$document['_id'],
    ];
}

echo json_encode($productsData);

?>