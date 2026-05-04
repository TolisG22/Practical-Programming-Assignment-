<?php
session_start();
require_once 'config.php';

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Επισκέπτης';

if ($user_id <= 0) {
    header("Location: auth.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $full_desc = "ΥΛΙΚΑ:\n" . $_POST['ingredients'] . "\n\nΕΚΤΕΛΕΣΗ:\n" . $_POST['instructions'];
    
    $image_name = time() . "_" . $_FILES['image']['name'];
    $target = "uploads/" . $image_name;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
        $sql = "INSERT INTO recipes (user_id, title, description, image_url) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $title, $full_desc, $image_name]);
        header("Location: index.php");
        exit();
    } else {
        $error = "Αποτυχία ανεβάσματος εικόνας.";
    }
}
?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodBlog - Νέα Συνταγή</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="index-body">

    <header class="main-header">
        <div class="header-container">
            <div class="nav-left">
                <span class="welcome-msg">👤 <strong><?php echo htmlspecialchars($username); ?></strong></span>
            </div>
            <div class="nav-center">
                <h1 class="main-title">🍳 FoodBlog</h1>
            </div>
            <div class="nav-right">
                <a href="index.php">🏠 Αρχική</a>
                <a href="profile.php">👤 Προφίλ</a>
            </div>
        </div>
    </header>

    <main class="container">
        <div class="upload-container">
            <div class="book-page upload-card">
                <h2 class="handwritten-title">📝 Νέα Συνταγή</h2>
                
                <?php if(isset($error)): ?>
                    <p class="error-msg"><?php echo $error; ?></p>
                <?php endif; ?>

                <form method="POST" action="upload.php" enctype="multipart/form-data" class="auth-form">
                    <input type="text" name="title" placeholder="Τίτλος συνταγής" required>
                    <textarea name="ingredients" placeholder="Υλικά (π.χ. 3 αυγά, 1 φλ. ζάχαρη...)" required></textarea>
                    <textarea name="instructions" placeholder="Εκτέλεση συνταγής (βήμα-βήμα)" required></textarea>
                    
                    <div class="file-input-wrapper">
                        <label for="image">📸 Φωτογραφία Πιάτου:</label>
                        <input type="file" name="image" id="image" accept="image/*" required>
                    </div>

                    <button type="submit" class="upload-submit-btn">Δημοσίευση</button>
                </form>
            </div>
        </div>
    </main>

</body>
</html>