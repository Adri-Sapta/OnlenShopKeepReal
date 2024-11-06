<?php
require_once __DIR__ . '/../config/database.php';
$db = new Database();

// Set header for JSON response
header('Content-Type: application/json');

// Read JSON input
$data = json_decode(file_get_contents('php://input'), true);

// Extract login details with null coalescing to handle undefined indexes
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

// Find user by email
$userCollection = $db->getCollection("Users");
$user = $userCollection->findOne(['email' => $email]);

if ($user) {
    // Verify password
    if (password_verify($password, $user['password'])) {
        // Login success
        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'user' => [
                'id' => (string)$user['_id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'phone' => $user['phone'],
                'role' => $user['role']
            ]
        ]);
    } else {
        // Incorrect password
        echo json_encode(['success' => false, 'error' => 'Invalid email or password.']);
    }
} else {
    // User not found
    echo json_encode(['success' => false, 'error' => 'User not found.']);
}
?>
