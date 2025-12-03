<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/config/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// কেবল logged-in user-ই এখানে আসতে পারবে
if (empty($_SESSION['user_id'])) {
    echo "YOU MUST BE LOGGED IN TO ADD A RECIPE<br>";
    echo '<a href="login.php">Login</a>';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title        = trim($_POST['title'] ?? '');
    $description  = trim($_POST['description'] ?? '');
    $ingredients  = trim($_POST['ingredients'] ?? '');
    $instructions = trim($_POST['instructions'] ?? '');

    if ($title === '' || $description === '' || $ingredients === '' || $instructions === '') {
        echo "ALL FIELDS REQUIRED<br>";
        exit;
    }

    try {
        $stmt = $pdo->prepare(
            'INSERT INTO recipes (user_id, title, description, ingredients, instructions)
             VALUES (?, ?, ?, ?, ?)'
        );
        $ok = $stmt->execute([
            $_SESSION['user_id'],
            $title,
            $description,
            $ingredients,
            $instructions
        ]);

        if ($ok) {
            echo "RECIPE INSERTED SUCCESSFULLY<br>";
            echo '<a href="recipes.php">Go to Recipes</a>';
            exit;
        } else {
            echo "INSERT FAILED<br>";
            exit;
        }
    } catch (PDOException $e) {
        echo "DB ERROR: " . $e->getMessage();
        exit;
    }
}
?>

<form method="post" action="">
    <input type="text" name="title" placeholder="Recipe Title"><br>
    <textarea name="description" placeholder="Short Description"></textarea><br>
    <textarea name="ingredients" placeholder="Ingredients (one per line)"></textarea><br>
    <textarea name="instructions" placeholder="Instructions"></textarea><br>
    <button type="submit">Add Recipe</button>
</form>