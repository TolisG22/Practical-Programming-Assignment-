<?php
session_start();
require_once 'config.php';

if (isset($_GET['id']) && isset($_SESSION['user_id'])) {
    // Διαγράφουμε τη συνταγή ΜΟΝΟ αν ανήκει στον συνδεδεμένο χρήστη (για ασφάλεια)
    $stmt = $pdo->prepare("DELETE FROM recipes WHERE id = ? AND user_id = ?");
    $stmt->execute([$_GET['id'], $_SESSION['user_id']]);
}

header("Location: profile.php");
exit();
?>