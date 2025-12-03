<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/config/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['user_id'])) {
    echo "YOU MUST BE LOGGED IN TO EDIT A RECIPE<br>";
    echo '<a href="login.php">Login</a>';
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    echo "INVALID RECIPE ID";
    exit;
}

// আগে DB থেকে recipe টা আনবো, যেন form এ পুরোনো মান দেখানো যায়
try {
    $stmt = $pdo->prepare(
        'SELECT * FROM recipes WHERE id = ?'
    );
    $stmt->execute([$id]);
    $recipe = $stmt->fetch();
} catch (PDOException $e) {
    die('DB ERROR: ' . $e->getMessage());
}

if (!$recipe) {
    echo "RECIPE NOT FOUND";
    exit;
}

// author check
if ($recipe['user_id'] != $_SESSION['user_id']) {
    echo "YOU ARE NOT ALLOWED TO EDIT THIS RECIPE";
    exit;
}

// POST এ update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title        = trim($_POST['title'] ?? '');
    $description  = trim($_POST['description'] ?? '');
    $ingredients  = trim($_POST['ingredients'] ?? '');
    $instructions = trim($_POST['instructions'] ?? '');

    if ($title === '' || $description === '' || $ingredients === '' || $instructions === '') {
        echo "ALL FIELDS REQUIRED<br>";
    } else {
        try {
            $stmt = $pdo->prepare(
                'UPDATE recipes
                 SET title = ?, description = ?, ingredients = ?, instructions = ?
                 WHERE id = ? AND user_id = ?'
            );
            $ok = $stmt->execute([
                $title,
                $description,
                $ingredients,
                $instructions,
                $id,
                $_SESSION['user_id']
            ]);

            if ($ok) {
                // detail পেইজে ফিরে যাই
                header('Location: recipe-details.php?id=' . $id);
                exit;
            } else {
                echo "UPDATE FAILED<br>";
            }
        } catch (PDOException $e) {
            echo "DB ERROR: " . $e->getMessage();
        }
    }
}
?>

<form method="post" action="">
    <input type="text" name="title" placeholder="Title"
           value="<?php echo htmlspecialchars($recipe['title']); ?>"><br>

    <textarea name="description" placeholder="Description"><?php
        echo htmlspecialchars($recipe['description']);
    ?></textarea><br>

    <textarea name="ingredients" placeholder="Ingredients"><?php
        echo htmlspecialchars($recipe['ingredients']);
    ?></textarea><br>

    <textarea name="instructions" placeholder="Instructions"><?php
        echo htmlspecialchars($recipe['instructions']);
    ?></textarea><br>

    <button type="submit">Save Changes</button>
</form>