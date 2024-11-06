<?php
require_once __DIR__ . '/../config/database.php';
$db = new Database();

// Read JSON input
$data = json_decode(file_get_contents('php://input'), true);

// Extract account details
$username = $data['username'];
$email = $data['email'];
$phone = $data['phone'];
$password = password_hash($data['password'], PASSWORD_DEFAULT); // Hash the password

// Check if email already exists
$userCollection = $db->getCollection("Users");
$existingUser = $userCollection->findOne(['email' => $email]);

if ($existingUser) {
    echo json_encode(['success' => false, 'error' => 'Email already exists.']);
    exit;
}

// Insert new user
$newUser = [
    'username' => $username,
    'email' => $email,
    'phone' => $phone,
    'password' => $password,
    'role' => 'user', // Default role
    'createdAt' => new MongoDB\BSON\UTCDateTime(),
    'updatedAt' => new MongoDB\BSON\UTCDateTime()
];

try {
    $userCollection->insertOne($newUser);
    echo json_encode(['success' => true, 'message' => 'Account created successfully.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Failed to create account: ' . $e->getMessage()]);
}
?>
