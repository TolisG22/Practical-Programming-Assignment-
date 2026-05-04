<?php
session_start();
require_once 'config.php';

// Αν ο χρήστης δεν είναι συνδεδεμένος, τον στέλνουμε στο login
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 1. Παίρνουμε τις συνταγές του χρήστη
$stmt = $pdo->prepare("SELECT * FROM recipes WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$my_recipes = $stmt->fetchAll();

// 2. Παίρνουμε τα σχόλια που έχουν γίνει στις συνταγές
$comment_sql = "SELECT c.*, u.username as commenter, r.title as recipe_title 
                FROM comments c 
                JOIN users u ON c.user_id = u.id 
                JOIN recipes r ON c.recipe_id = r.id 
                WHERE r.user_id = ? 
                ORDER BY c.created_at DESC";
$c_stmt = $pdo->prepare($comment_sql);
$c_stmt->execute([$user_id]);
$feedbacks = $c_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <title>Το Προφίλ μου - FoodBlog</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #f4f4f4; margin: 0; padding: 20px; }
        .container { max-width: 1000px; margin: auto; }
        header { background: #333; color: white; padding: 15px; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        header a { color: #ffa500; text-decoration: none; font-weight: bold; }
        h2 { border-left: 5px solid #ffa500; padding-left: 15px; color: #333; margin-top: 40px; }
        
        .recipe-grid { display: flex; flex-wrap: wrap; gap: 20px; }
        .recipe-card { background: white; border-radius: 10px; width: 300px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); overflow: hidden; display: flex; flex-direction: column; }
        .recipe-img { width: 100%; height: 180px; object-fit: cover; }
        
        .recipe-body { padding: 15px; flex-grow: 1; }
        .recipe-body h4 { margin: 0 0 10px 0; color: #333; }
        .recipe-body p { font-size: 0.9rem; color: #666; line-height: 1.4; margin: 0; }
        
        .toggle-btn { 
            background: none; border: none; color: #ffa500; 
            cursor: pointer; font-weight: bold; padding: 0; 
            margin-top: 10px; font-size: 0.85rem; 
        }

        .recipe-footer { padding: 10px 15px; background: #fafafa; border-top: 1px solid #eee; text-align: right; }
        .delete-btn { color: #ff4d4d; text-decoration: none; font-weight: bold; font-size: 0.85rem; }
        
        .feedback-section { background: white; padding: 20px; border-radius: 10px; margin-top: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .comment-item { padding: 12px; border-bottom: 1px solid #eee; }
        .comment-meta { font-size: 0.8rem; color: #888; margin-bottom: 5px; }
    </style>
</head>
<body>

<div class="container">
    <header>
        <span>👤 Προφίλ χρήστη: <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></span>
        
        <nav>
            <a href="index.php">🏠 Αρχική</a>
            <a href="favorites.php" style="margin-left:20px;">❤️ Αγαπημένα</a>
            <a href="logout.php" style="margin-left:20px; color:#ff4d4d;">Έξοδος</a>
        </nav>
    </header>

    <h2>🍳 Οι Δημοσιευμένες Συνταγές μου</h2>
    <div class="recipe-grid">
        <?php if (empty($my_recipes)): ?>
            <p style="padding-left: 20px;">Δεν έχετε ανεβάσει ακόμα συνταγές.</p>
        <?php else: ?>
            <?php foreach ($my_recipes as $recipe): ?>
                <div class="recipe-card">
                    <?php if($recipe['image_url']): ?>
                        <img src="uploads/<?php echo htmlspecialchars($recipe['image_url']); ?>" class="recipe-img">
                    <?php endif; ?>
                    
                    <div class="recipe-body">
                        <h4><?php echo htmlspecialchars($recipe['title']); ?></h4>
                        
                        <div id="short-<?php echo $recipe['id']; ?>">
                            <p><?php echo mb_strimwidth(htmlspecialchars($recipe['description']), 0, 100, "..."); ?></p>
                        </div>

                        <div id="full-<?php echo $recipe['id']; ?>" style="display: none;">
                            <p><?php echo nl2br(htmlspecialchars($recipe['description'])); ?></p>
                        </div>

                        <button class="toggle-btn" onclick="toggleRecipe(<?php echo $recipe['id']; ?>)" id="btn-<?php echo $recipe['id']; ?>">
                            Περισσότερα...
                        </button>
                    </div>
                    
                    <div class="recipe-footer">
                        <a href="delete_recipe.php?id=<?php echo $recipe['id']; ?>" class="delete-btn" onclick="return confirm('Σίγουρα διαγραφή;')">🗑️ Διαγραφή</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <h2>💬 Feedback από την Κοινότητα</h2>
    <div class="feedback-section">
        <?php if (empty($feedbacks)): ?>
            <p>Δεν υπάρχουν ακόμα σχόλια για τις συνταγές σας.</p>
        <?php else: ?>
            <?php foreach ($feedbacks as $fb): ?>
                <div class="comment-item">
                    <div class="comment-meta">
                        Ο χρήστης <strong><?php echo htmlspecialchars($fb['commenter']); ?></strong> 
                        είπε στη συνταγή <strong>"<?php echo htmlspecialchars($fb['recipe_title']); ?>"</strong>:
                    </div>
                    <div class="comment-text">
                        "<?php echo htmlspecialchars($fb['comment_text']); ?>"
                    </div>
                    <small style="color:#aaa;"><?php echo $fb['created_at']; ?></small>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleRecipe(id) {
    const fullText = document.getElementById('full-' + id);
    const shortText = document.getElementById('short-' + id);
    const btn = document.getElementById('btn-' + id);

    if (fullText.style.display === "none") {
        fullText.style.display = "block";
        shortText.style.display = "none";
        btn.innerText = "Λιγότερα";
    } else {
        fullText.style.display = "none";
        shortText.style.display = "block";
        btn.innerText = "Περισσότερα...";
    }
}
</script>

</body>
</html>