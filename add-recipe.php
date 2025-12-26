<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/config/db.php';
session_start();

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$error = '';

// Fetch categories
$stmt = $pdo->prepare('SELECT id, name, icon FROM categories ORDER BY name');
$stmt->execute();
$categories = $stmt->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $ingredients = trim($_POST['ingredients'] ?? '');
    $instructions = trim($_POST['instructions'] ?? '');
    
    // Image upload
    $imagePath = null;
    if (!empty($_FILES['image']['name'])) {
        $uploadDir = __DIR__ . '/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $filename = time() . '_' . $_FILES['image']['name'];
        $uploadPath = $uploadDir . $filename;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
            $imagePath = 'uploads/' . $filename;
        }
    }
    
    if (!$title || !$ingredients || !$instructions || !$category_id) {
        $error = "All fields are required";
    } else {
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO recipes (user_id, title, description, category_id, image, ingredients, instructions) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([$_SESSION['user_id'], $title, $description, $category_id, $imagePath, $ingredients, $instructions]);
            
            // Add points
            $pdo->query("UPDATE users SET points = points + 50 WHERE id = " . $_SESSION['user_id']);
            $_SESSION['user_points'] = ($_SESSION['user_points'] ?? 0) + 50;
            
            header('Location: recipe-details.php?id=' . $pdo->lastInsertId());
            exit;
        } catch (PDOException $e) {
            $error = $e->getMessage();
        }
    }
}
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<main>
    <section class="container" style="max-width: 700px; margin: 40px auto;">
        <h1>Add New Recipe</h1>
        
        <?php if ($error): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 12px; border-radius: 6px; margin-bottom: 16px;">
                ❌ <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <form method="post" enctype="multipart/form-data">
            <div style="margin-bottom: 16px;">
                <label>Title:</label><br>
                <input type="text" name="title" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            </div>
            
            <div style="margin-bottom: 16px;">
                <label>Description:</label><br>
                <textarea name="description" rows="3" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"></textarea>
            </div>
            
            <div style="margin-bottom: 16px;">
                <label>Category:</label><br>
                <select name="category_id" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    <option value="">-- Select a category --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>">
                            <?php echo $cat['icon']; ?> <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div style="margin-bottom: 16px;">
                <label>Recipe Image:</label><br>
                <input type="file" name="image" accept="image/*" style="padding: 8px;">
            </div>
            
            <div style="margin-bottom: 16px;">
                <label>Ingredients:</label><br>
                <textarea name="ingredients" rows="6" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"></textarea>
            </div>
            
            <div style="margin-bottom: 16px;">
                <label>Instructions:</label><br>
                <textarea name="instructions" rows="8" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"></textarea>
            </div>
            
            <button type="submit" style="background: #4CAF50; color: white; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer;">
                Post Recipe
            </button>
            
            <a href="recipes.php">Cancel</a>
        </form>
    </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>