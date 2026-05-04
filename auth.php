<?php
session_start();
require_once 'config.php';

$error = "";
$message = "";

// Επεξεργασία LOGIN
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login_submit'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header("Location: index.php");
        exit();
    } else {
        $error = "Λάθος username ή κωδικός!";
    }
}

// Επεξεργασία REGISTER
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register_submit'])) {
    $user = $_POST['username'];
    $email = $_POST['email'];
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    try {
        $stmt->execute([$user, $email, $pass]);
        $message = "Η εγγραφή ολοκληρώθηκε! Συνδεθείτε.";
    } catch (PDOException $e) {
        $error = "Το username ή το email υπάρχει ήδη.";
    }
}
?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <title>Το Βιβλίο των Συνταγών μας - FoodBlog</title>
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@600&family=Indie+Flower&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<div class="book-container">
    <div class="book-inner" id="authCard">
        
        <div class="book-page login-page">
            <div class="page-content">
                <div class="page-header">
                    <h2>📖 Οι συνταγές μας</h2>
                    <p class="handwritten-note">Καλωσήρθες πίσω, σεφ!</p>
                </div>
                
                <form class="auth-form" action="auth.php" method="POST">
                    <input type="text" name="username" placeholder="Όνομα Χρήστη" required>
                    <input type="password" name="password" placeholder="Κωδικός" required>
                    <?php if($error) echo "<p class='error-msg' style='color:red;'>$error</p>"; ?>
                    <button type="submit" name="login_submit">Είσοδος</button>
                </form>
                
                <div class="auth-footer">
                    Νέος στην κουζίνα; <br>
                    <a href="javascript:void(0)" onclick="toggleFlip()">Εντάξου στην ομάδα μας</a>
                </div>
            </div>
        </div>

        <div class="book-page register-page">
            <div class="page-content">
                <div class="page-header">
                    <h2>📝 Νέος σεφ</h2>
                    <p class="handwritten-note">Πρόσθεσε τα στοιχεία σου...</p>
                </div>

                <form id="regForm" class="auth-form" action="auth.php" method="POST">
                    <input type="text" name="username" placeholder="Όνομα Χρήστη" required>
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Κωδικός (6+ χαρακτήρες)" required>
                    <button type="submit" name="register_submit" class="register-btn">Εγγραφή</button>
                </form>
                
                <div class="auth-footer">
                    Είσαι ήδη στην ομάδα μας; <br>
                    <a href="javascript:void(0)" onclick="toggleFlip()">Σύνδεση</a>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    function toggleFlip() {
        document.getElementById('authCard').classList.toggle('flipped');
    }

    <?php if($message): ?>
        Swal.fire({
            title: 'Επιτυχία!',
            text: '<?php echo $message; ?>',
            icon: 'success',
            confirmButtonColor: '#8d2b2b'
        });
    <?php endif; ?>
</script>
</body>
</html>