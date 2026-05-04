<?php
// 1. Ξεκινάμε το session για να έχουμε πρόσβαση σε αυτό που θέλουμε να διαγράψουμε
session_start();

// 2. Αδειάζουμε όλες τις μεταβλητές του session
$_SESSION = array();

// 3. Καταστρέφουμε το session οριστικά
session_destroy();

// 4. Ανακατευθύνουμε τον χρήστη στην αρχική σελίδα
header("Location: index.php");
exit();
?>