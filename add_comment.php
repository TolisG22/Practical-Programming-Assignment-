<?php
session_start();
require_once 'config.php';

$data = json_decode(file_get_contents('php://input'), true);

if (isset($_SESSION['user_id']) && !empty($data['text']) && !empty($data['recipe_id'])) {
    $stmt = $pdo->prepare("INSERT INTO comments (recipe_id, user_id, comment_text) VALUES (?, ?, ?)");
    $stmt->execute([$data['recipe_id'], $_SESSION['user_id'], $data['text']]);
    
    // Επιστρέφουμε success και το username του συνδεδεμένου χρήστη
    echo json_encode([
        'status' => 'success', 
        'user' => $_SESSION['username']
    ]);
} else {
    echo json_encode(['status' => 'error']);
}
?>