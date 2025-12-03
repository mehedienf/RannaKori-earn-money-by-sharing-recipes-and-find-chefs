<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/config/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['user_id'])) {
    echo "YOU MUST BE LOGGED IN TO DELETE A RECIPE<br>";
    echo '<a href="login.php">Login</a>';
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    echo "INVALID RECIPE ID";
    exit;
}

// আগে recipe টা আনি, যেন author check করতে পারি
try {
    $stmt = $pdo->prepare('SELECT * FROM recipes WHERE id = ?');
    $stmt->execute([$id]);
    $recipe = $stmt->fetch();
} catch (PDOException $e) {
    die('DB ERROR: ' . $e->getMessage());
}

if (!$recipe) {
    echo "RECIPE NOT FOUND";
    exit;
}

if ($recipe['user_id'] != $_SESSION['user_id']) {
    echo "YOU ARE NOT ALLOWED TO DELETE THIS RECIPE";
    exit;
}

// যদি GET request, আগে simple confirm দেখাই
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    ?>
    <h2>Are you sure you want to delete this recipe?</h2>
    <p><?php echo htmlspecialchars($recipe['title']); ?></p>
    <form method="post" action="">
        <button type="submit" name="confirm" value="yes">Yes, delete</button>
        <a href="recipe-details.php?id=<?php echo (int)$recipe['id']; ?>">Cancel</a>
    </form>
    <?php
    exit;
}

// POST এ সত্যি delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['confirm'] !== 'yes') {
        header('Location: recipe-details.php?id=' . $id);
        exit;
    }

    try {
        $stmt = $pdo->prepare('DELETE FROM recipes WHERE id = ? AND user_id = ?');
        $ok = $stmt->execute([$id, $_SESSION['user_id']]);

        if ($ok) {
            header('Location: recipes.php');
            exit;
        } else {
            echo "DELETE FAILED<br>";
        }
    } catch (PDOException $e) {
        echo "DB ERROR: " . $e->getMessage();
    }
}