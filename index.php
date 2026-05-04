<?php
session_start();
require_once 'config.php';

// Σωστός ορισμός μεταβλητών
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Επισκέπτης';

$sql = "SELECT r.*, u.username, 
        (SELECT COUNT(*) FROM likes l WHERE l.recipe_id = r.id AND l.user_id = ?) as user_liked
        FROM recipes r
        JOIN users u ON r.user_id = u.id 
        ORDER BY r.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$recipes = $stmt->fetchAll();

// Φέρνουμε Ολα τα σχόλια
$comment_sql = "SELECT c.*, u.username FROM comments c 
                JOIN users u ON c.user_id = u.id 
                ORDER BY c.created_at ASC";
$comment_stmt = $pdo->prepare($comment_sql);
$comment_stmt->execute();
$all_comments = $comment_stmt->fetchAll();

$comments_by_recipe = [];
foreach ($all_comments as $com) {
    $comments_by_recipe[$com['recipe_id']][] = $com;
}
?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodBlog - Η Κουζίνα μας</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="index-body">

    <header class="main-header">
    <div class="header-container">
        
        <div class="nav-left">
    <?php if ($user_id > 0): ?>
        <span class="welcome-msg">
           👤 Καλωσόρισες, <strong><?php echo htmlspecialchars($username); ?></strong>
        </span>
    <?php else: ?>
        <a href="auth.php" class="auth-link" style="color:#ffa500;">Σύνδεση / Εγγραφή</a>
    <?php endif; ?>
    </div>

        <div class="nav-center">
            <h1 class="main-title">🍳 FoodBlog</h1>
        </div>

        <div class="nav-right">
            <?php if ($user_id > 0): ?>
                <a href="favorites.php" class="nav-link-fav"></i>❤️ Αγαπημένα</a> 
                <a href="profile.php" class="nav-link-profile"></i> 👤 Προφίλ</a> 
                <a href="upload.php" class="nav-link-new"></i>➕ Νέα Συνταγή</a>
                <a href="logout.php" style="color:#ff4d4d;">Έξοδος</a>
            <?php else: ?>
                <div style="flex: 1;"></div> 
            <?php endif; ?>
        </div>

    </div>
    </header>

    <main class="container">
    <div class="search-section">
        <div class="search-container">
            <i class="fas fa-search search-icon"></i>
            <input type="text" id="recipeSearch" placeholder="Αναζήτηση συνταγής..." onkeyup="filterRecipes()">
        </div>
    </div>

    <div class="book-slider-wrapper">
        <button class="nav-btn prev-btn" onclick="moveSlide(-1)">❮</button>
        
        <div class="recipe-slider" id="recipeSlider">
            <?php foreach($recipes as $index => $recipe): ?>
                <?php 
                    $parts = explode("ΕΚΤΕΛΕΣΗ:", $recipe['description']);
                    $ingredients = isset($parts[0]) ? $parts[0] : $recipe['description'];
                    $instructions = isset($parts[1]) ? "ΕΚΤΕΛΕΣΗ:" . $parts[1] : "";
                ?>
                
                <div class="recipe-slide <?php echo ($index === 0) ? 'active' : ''; ?>" data-id="<?php echo $recipe['id']; ?>">
                    <article class="recipe-card book-style">
                        
                        <div class="book-spine-shadow"></div>

                        <div class="page page-left">
                            <div class="recipe-img-wrapper">
                                <img src="uploads/<?php echo htmlspecialchars($recipe['image_url']); ?>" class="recipe-img">
                            </div>
                            <div class="page-text">
                                <h3><?php echo htmlspecialchars($recipe['title']); ?></h3>
                                <p class="ingredients-list"><?php echo nl2br(htmlspecialchars(trim($ingredients))); ?></p>
                            </div>
                        </div>

                        <div class="page page-right">
                            <div class="page-text">
                                <p class="instructions-text"><?php echo nl2br(htmlspecialchars(trim($instructions))); ?></p>
                            </div>
                            
                            <div class="comments-inside-book">
                                <h4>💬 Σχόλια</h4>
                                <div class="comments-list">
                                    <?php if(isset($comments_by_recipe[$recipe['id']])): ?>
                                        <?php foreach($comments_by_recipe[$recipe['id']] as $com): ?>
                                            <div class="single-comment">
                                                <strong><?php echo htmlspecialchars($com['username']); ?>:</strong> 
                                                <span><?php echo htmlspecialchars($com['comment_text']); ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                <input type="text" class="comment-box" data-recipe="<?php echo $recipe['id']; ?>" placeholder="Πρόσθεσε ένα σχόλιο...">
                            </div>

                            <div class="recipe-footer">
                                <button class="like-btn <?php echo ($recipe['user_liked'] > 0) ? 'active' : ''; ?>" data-id="<?php echo $recipe['id']; ?>">
                                    <?php echo ($recipe['user_liked'] > 0) ? '❤️ Liked' : '🤍 Like'; ?>
                                </button>
                                <div class="recipe-date-bottom"><?php echo date('d/m/Y', strtotime($recipe['created_at'])); ?></div>
                            </div>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>

        <button class="nav-btn next-btn" onclick="moveSlide(1)">❯</button>
    </div>
</main>

    <script src="script.js"></script>
</body>
</html>