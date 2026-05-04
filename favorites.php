<?php
session_start();
require_once 'config.php';

// Αν ο χρήστης δεν είναι συνδεδεμένος, τον στέλνουμε στο login
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// SQL για να φέρουμε τις συνταγές που έχει κάνει LIKE ο χρήστης
$sql = "SELECT r.*, u.username 
        FROM recipes r
        JOIN likes l ON r.id = l.recipe_id
        JOIN users u ON r.user_id = u.id
        WHERE l.user_id = ?
        ORDER BY l.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$fav_recipes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <title>Τα Αγαπημένα μου - FoodBlog</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #f4f4f4; margin: 0; padding: 20px; }
        .container { max-width: 1000px; margin: auto; }
        header { background: #333; color: white; padding: 15px; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        header a { color: #ffa500; text-decoration: none; font-weight: bold; margin-left: 15px; }
        
        h2 { border-left: 5px solid #ff4d4d; padding-left: 15px; color: #333; }
        
        .recipe-grid { display: flex; flex-wrap: wrap; gap: 20px; }
        .recipe-card { background: white; border-radius: 10px; width: 300px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); overflow: hidden; }
        .recipe-img { width: 100%; height: 180px; object-fit: cover; }
        .recipe-body { padding: 15px; }
        .recipe-body h4 { margin: 0; color: #333; }
        .author { font-size: 0.8rem; color: #888; margin-bottom: 10px; }
    </style>
</head>
<body>

<div class="container">
    <header>
        <span>❤️ Τα Αγαπημένα του/της <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></span>
        <nav>
            <a href="index.php">🏠 Αρχική</a>
            <a href="profile.php">👤 Προφίλ</a>
        </nav>
    </header>

    <h2>🌟 Συνταγές που σας άρεσαν</h2>
    
    <div class="recipe-grid">
        <?php if (empty($fav_recipes)): ?>
            <p style="padding-left: 20px;">Δεν έχετε προσθέσει ακόμα καμία συνταγή στα αγαπημένα.</p>
        <?php else: ?>
            <?php foreach ($fav_recipes as $recipe): ?>
                <div class="recipe-card">
                    <?php if($recipe['image_url']): ?>
                        <img src="uploads/<?php echo htmlspecialchars($recipe['image_url']); ?>" class="recipe-img">
                    <?php endif; ?>
                    
                    <div class="recipe-body">
                        <h4><?php echo htmlspecialchars($recipe['title']); ?></h4>
                        <div class="author">Δημιουργός: <?php echo htmlspecialchars($recipe['username']); ?></div>
                        <p style="font-size: 0.9rem; color: #555;">
                            <?php echo mb_strimwidth(htmlspecialchars($recipe['description']), 0, 100, "..."); ?>
                        </p>
                        <a href="index.php?id=<?php echo $recipe['id']; ?>" style="font-size: 0.8rem; color: #ffa500; text-decoration: none; font-weight: bold;">
    📖 Προβολή στο Βιβλίο
</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

</body>
</html>