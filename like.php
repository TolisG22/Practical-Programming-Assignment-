<?php
session_start();
require_once 'config.php';

// Λήψη των δεδομένων JSON από την JavaScript
$data = json_decode(file_get_contents('php://input'), true);

if (isset($_SESSION['user_id']) && isset($data['recipe_id'])) {
    $user_id = $_SESSION['user_id'];
    $recipe_id = $data['recipe_id'];

    // Έλεγχος αν υπάρχει ήδη το like
    $stmt = $pdo->prepare("SELECT id FROM likes WHERE user_id = ? AND recipe_id = ?");
    $stmt->execute([$user_id, $recipe_id]);
    
    if ($stmt->fetch()) {
        // Αν υπάρχει, το σβήνουμε
        $stmt = $pdo->prepare("DELETE FROM likes WHERE user_id = ? AND recipe_id = ?");
        $stmt->execute([$user_id, $recipe_id]);
        echo json_encode(['status' => 'unliked']);
    } else {
        // Αν δεν υπάρχει, το προσθέτουμε
        $stmt = $pdo->prepare("INSERT INTO likes (user_id, recipe_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $recipe_id]);
        echo json_encode(['status' => 'liked']);
    }
}
?>