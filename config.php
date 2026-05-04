<?php
// Σύνδεση με τη βάση δεδομένων
$host = 'localhost';
$db   = 'snor_db';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Αποτυχία σύνδεσης: " . $e->getMessage());
}
?>