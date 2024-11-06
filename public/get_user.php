<?php
require_once __DIR__ . '/../config/database.php';
$db = new Database();

// Get the userId from the query parameter
$userId = $_GET['userId'] ?? '';
if (!preg_match('/^[a-f\d]{24}$/i', $userId)) {
    echo json_encode(['error' => 'Invalid User ID format.']);
    exit;
}

// Define the collection for users
$userCollection = $db->getCollection("Users");

try {
    // Find the user by userId
    $user = $userCollection->findOne(['_id' => new MongoDB\BSON\ObjectId($userId)]);

    if ($user) {
        // Prepare user data to return as JSON
        $userData = [
            '_id' => (string)$user['_id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'phone' => $user['phone'],
            'role' => $user['role'],
            'createdAt' => $user['createdAt']->toDateTime()->format('Y-m-d H:i:s'),
            'updatedAt' => $user['updatedAt']->toDateTime()->format('Y-m-d H:i:s')
        ];

        echo json_encode($userData);
    } else {
        echo json_encode(['error' => 'User not found.']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => 'An error occurred: ' . $e->getMessage()]);
}
?>