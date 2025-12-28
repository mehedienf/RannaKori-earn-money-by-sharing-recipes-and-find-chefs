<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/config/db.php';

$query = 'SELECT r.id, r.title, r.description, r.image, r.created_at, r.user_id, 
                 u.name AS author_name, u.avatar AS author_avatar
          FROM recipes r 
          INNER JOIN users u ON r.user_id = u.id
          ORDER BY r.created_at DESC';

$stmt = $pdo->prepare($query);
$stmt->execute();
$recipes = $stmt->fetchAll();

echo "DEBUG: " . count($recipes) . " recipes returned from query\n";
echo "=================================\n";

foreach ($recipes as $index => $recipe) {
    echo "Index $index: ID=" . $recipe['id'] . " | Title=" . $recipe['title'] . " | User=" . $recipe['author_name'] . "\n";
}

echo "=================================\n";
echo "Array dump:\n";
var_dump($recipes);
?>
