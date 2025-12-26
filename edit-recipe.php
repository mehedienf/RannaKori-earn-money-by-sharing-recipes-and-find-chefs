<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/config/db.php';
session_start();

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$id = (int)($_GET['id'] ?? 0);
$error = '';

// Fetch recipe
$stmt = $pdo->prepare('SELECT * FROM recipes WHERE id = ? AND user_id = ?');
$stmt->execute([$id, $_SESSION['user_id']]);
$recipe = $stmt->fetch();

if (!$recipe) {
    die('Recipe not found');
}

// Fetch categories
$stmt = $pdo->prepare('SELECT id, name, icon FROM categories ORDER BY name');
$stmt->execute();
$categories = $stmt->fetchAll();

// Update
if ($_POST) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category_id = (int)($_POST['category_id'] ?? 0);
    $ingredients = trim($_POST['ingredients']);
    $instructions = trim($_POST['instructions']);
    $imagePath = $recipe['image'];
    
    // New image upload
    if (!empty($_FILES['image']['name'])) {
        $uploadDir = __DIR__ . '/uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        
        $filename = time() . '_' . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename);
        $imagePath = 'uploads/' . $filename;
    }
    
    if ($title && $ingredients && $instructions && $category_id) {
        $stmt = $pdo->prepare('UPDATE recipes SET title=?, description=?, category_id=?, image=?, ingredients=?, instructions=? WHERE id=?');
        $stmt->execute([$title, $description, $category_id, $imagePath, $ingredients, $instructions, $id]);
        
        header('Location: recipe-details.php?id=' . $id);
        exit;
    } else {
        $error = "All fields required";
    }
}
?>



<main>
    <section class="container" style="max-width: 700px; margin: 40px auto;">
        <h1>Edit Recipe</h1>
        
        <?php if ($error): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 12px; border-radius: 6px; margin-bottom: 16px;">
                ❌ <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <form method="post" enctype="multipart/form-data">
            <div style="margin-bottom: 16px;">
                <label>Title:</label><br>
                <input type="text" name="title" required value="<?php echo htmlspecialchars($recipe['title']); ?>" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            </div>
            
            <div style="margin-bottom: 16px;">
                <label>Description:</label><br>
                <textarea name="description" rows="3" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"><?php echo htmlspecialchars($recipe['description']); ?></textarea>
            </div>
            
            <div style="margin-bottom: 16px;">
                <label>Category:</label><br>
                <select name="category_id" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    <option value="">-- Select a category --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $recipe['category_id'] == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo $cat['icon']; ?> <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div style="margin-bottom: 16px;">
                <label>Image:</label><br>
                <?php if ($recipe['image']): ?>
                    <img src="<?php echo $recipe['image']; ?>" style="max-width: 200px; margin: 10px 0;"><br>
                <?php endif; ?>
                <input type="file" name="image" accept="image/*">
            </div>
            
            <div style="margin-bottom: 16px;">
                <label>Ingredients:</label><br>
                <textarea name="ingredients" rows="6" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"><?php echo htmlspecialchars($recipe['ingredients']); ?></textarea>
            </div>
            
            <div style="margin-bottom: 16px;">
                <label>Instructions:</label><br>
                <textarea name="instructions" rows="8" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"><?php echo htmlspecialchars($recipe['instructions']); ?></textarea>
            </div>
            
            <button type="submit" style="background: #4CAF50; color: white; padding: 12px 24px; border: none; cursor: pointer;">
                Update
            </button>
            <a href="recipe-details.php?id=<?php echo $id; ?>">Cancel</a>
        </form>
    </section>
</main>

