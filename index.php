<?php
error_reporting(E_ALL); //error reporting on
ini_set('display_errors', 1); //display errors on

// database connection and session start korchi
require __DIR__ . '/config/db.php';
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
?>

<!-- header include -->
<?php include __DIR__ . '/includes/header.php'; ?>

<main>
  <!-- Hero Section -->
  <section style="background: linear-gradient(135deg, #4CAF50, #45a049); color: white; padding: 80px 20px; text-align: center;">
    <div class="container">
      <h1 style="font-size: 3rem; margin-bottom: 16px;">🍳 Welcome to Ranna-Kori</h1>
      <p style="font-size: 1.2rem; margin-bottom: 32px;">Share your favorite Bengali recipes and earn points</p>
      <div style="display: flex; gap: 16px; justify-content: center;">
        <a href="recipes.php" style="background: white; color: #4CAF50; padding: 12px 32px; border-radius: 4px; text-decoration: none; font-weight: bold;">
          Explore Recipes
        </a>
        <?php if (!empty($_SESSION['user_id'])): ?>
          <a href="add-recipe.php" style="background: transparent; color: white; padding: 12px 32px; border: 2px solid white; border-radius: 4px; text-decoration: none; font-weight: bold;">
            Share Your Recipe
          </a>
        <?php else: ?>
          <a href="register.php" style="background: transparent; color: white; padding: 12px 32px; border: 2px solid white; border-radius: 4px; text-decoration: none; font-weight: bold;">
            Create Account
          </a>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- Featured Recipes Section -->
  <section style="padding: 60px 20px; background: #f5f5f5;">
    <div class="container" style="max-width: 1200px; margin: 0 auto;">
      <h2 style="font-size: 2rem; margin-bottom: 40px; text-align: center;">⭐ Featured Recipes</h2>

      <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
        <?php
        // Fetch most liked featured recipe
        try {
          $query = 'SELECT r.id, r.title, r.description, r.image, r.created_at, r.user_id, 
                             u.name AS author_name,
                             COUNT(l.id) as like_count
                      FROM recipes r 
                      JOIN users u ON r.user_id = u.id 
                      LEFT JOIN likes l ON r.id = l.recipe_id
                      GROUP BY r.id
                      ORDER BY like_count DESC, r.created_at DESC
                      LIMIT 1';

          $stmt = $pdo->prepare($query);
          $stmt->execute();
          $recipes = $stmt->fetchAll();

          if (!empty($recipes)):
            foreach ($recipes as $recipe):
        ?>
              <!-- Recipe Card -->
              <article style="background: white; border: 1px solid #ddd; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); transition: transform 0.3s;">

                <!-- Recipe Image -->
                <?php if (!empty($recipe['image'])): ?>
                  <img src="<?php echo htmlspecialchars($recipe['image']); ?>"
                    style="width: 100%; height: 200px; object-fit: cover;">
                <?php else: ?>
                  <div style="width: 100%; height: 200px; background: #ddd; display: flex; align-items: center; justify-content: center; font-size: 3rem;">
                    🍳
                  </div>
                <?php endif; ?>

                <!-- Recipe Info -->
                <div style="padding: 16px;">
                  <h3 style="margin: 0 0 8px 0;">
                    <a href="recipe-details.php?id=<?php echo (int)$recipe['id']; ?>" style="text-decoration: none; color: #333;">
                      <?php echo htmlspecialchars($recipe['title']); ?>
                    </a>
                  </h3>

                  <p style="color: #666; font-size: 0.9rem; margin: 0 0 8px 0;">
                    By <strong><?php echo htmlspecialchars($recipe['author_name']); ?></strong>
                  </p>

                  <?php if (!empty($recipe['description'])): ?>
                    <p style="color: #555; font-size: 0.9rem; margin: 0 0 12px 0;">
                      <?php echo htmlspecialchars(substr($recipe['description'], 0, 70)); ?>...
                    </p>
                  <?php endif; ?>

                  <div style="display: flex; justify-content: space-between; align-items: center;">
                    <p style="color: #ff4444; margin: 0; font-weight: bold;">
                      ❤️ <?php echo (int)$recipe['like_count']; ?> Likes
                    </p>
                    <a href="recipe-details.php?id=<?php echo (int)$recipe['id']; ?>"
                      style="background: #4CAF50; color: white; padding: 8px 16px; border-radius: 4px; text-decoration: none; font-size: 0.9rem;">
                      View
                    </a>
                  </div>
                </div>
              </article>
            <?php
            endforeach;
          else:
            ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 40px;">
              <p style="color: #666; font-size: 1.1rem;">No recipes yet. Be the first to share!</p>
              <a href="add-recipe.php" style="background: #4CAF50; color: white; padding: 10px 20px; border-radius: 4px; text-decoration: none; display: inline-block; margin-top: 16px;">
                Add Recipe
              </a>
            </div>
          <?php endif; ?>
        <?php
        } catch (PDOException $e) {
          echo "<div style='color: red; padding: 20px; grid-column: 1 / -1;'><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</div>";
        }
        ?>
      </div>

      <div style="text-align: center; margin-top: 40px;">
        <a href="recipes.php" style="background: #4CAF50; color: white; padding: 12px 32px; border-radius: 4px; text-decoration: none; font-weight: bold; font-size: 1.1rem;">
          View All Recipes →
        </a>
      </div>
    </div>
  </section>

  <!-- Points & Rewards Section -->
  <section style="padding: 60px 20px; background: white;">
    <div class="container" style="max-width: 1200px; margin: 0 auto;">
      <h2 style="font-size: 2rem; margin-bottom: 40px; text-align: center;">💰 How to Earn Points</h2>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
        <div style="background: #f0f8f0; padding: 30px; border-radius: 8px; text-align: center; border-left: 4px solid #4CAF50;">
          <div style="font-size: 2.5rem; margin-bottom: 12px;">📝</div>
          <h3 style="margin-bottom: 8px;">Post Recipe</h3>
          <p style="color: #666; font-size: 1.2rem; font-weight: bold; margin: 0;">+50 Points</p>
        </div>

        <div style="background: #f0f8f0; padding: 30px; border-radius: 8px; text-align: center; border-left: 4px solid #4CAF50;">
          <div style="font-size: 2.5rem; margin-bottom: 12px;">💬</div>
          <h3 style="margin-bottom: 8px;">Write Review</h3>
          <p style="color: #666; font-size: 1.2rem; font-weight: bold; margin: 0;">+10 Points</p>
        </div>

        <div style="background: #f0f8f0; padding: 30px; border-radius: 8px; text-align: center; border-left: 4px solid #4CAF50;">
          <div style="font-size: 2.5rem; margin-bottom: 12px;">❤️</div>
          <h3 style="margin-bottom: 8px;">Get Liked</h3>
          <p style="color: #666; font-size: 1.2rem; font-weight: bold; margin: 0;">+1 Point</p>
        </div>

        <div style="background: #f0f8f0; padding: 30px; border-radius: 8px; text-align: center; border-left: 4px solid #4CAF50;">
          <div style="font-size: 2.5rem; margin-bottom: 12px;">💳</div>
          <h3 style="margin-bottom: 8px;">Redeem</h3>
          <p style="color: #666; font-size: 1.2rem; font-weight: bold; margin: 0;">100 Points = 10 TK</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Categories Section -->
  <section style="z-index: 1; padding: 60px 20px; background: white;">
    <div class="container" style="max-width: 1200px; margin: 0 auto;">
      <h2 style="font-size: 2rem; margin-bottom: 40px; text-align: center;">📚 Browse by Category</h2>

      <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px;">
        <?php
        try {
          $stmt = $pdo->prepare('SELECT id, name, slug, icon FROM categories ORDER BY name');
          $stmt->execute();
          $allCategories = $stmt->fetchAll();

          foreach ($allCategories as $cat):
            // Count recipes in this category
            $countStmt = $pdo->prepare('SELECT COUNT(*) as count FROM recipes WHERE category_id = ?');
            $countStmt->execute([$cat['id']]);
            $count = $countStmt->fetch()['count'];
        ?>
            <a href="category.php?slug=<?php echo htmlspecialchars($cat['slug']); ?>"
              style="display: block; background: linear-gradient(135deg, #4CAF50, #45a049); color: white; padding: 30px; border-radius: 8px; text-align: center; text-decoration: none; transition: transform 0.3s;">
              <div style="font-size: 2.5rem; margin-bottom: 12px;"><?php echo $cat['icon']; ?></div>
              <div style="font-weight: bold; margin-bottom: 8px;"><?php echo htmlspecialchars($cat['name']); ?></div>
              <div style="z-index: 1; font-size: 0.9rem; opacity: 0.9;"><?php echo $count; ?> recipe<?php echo $count !== 1 ? 's' : ''; ?></div>
            </a>
          <?php endforeach; ?>
        <?php } catch (PDOException $e) { ?>
          <div style="color: red;">Error loading categories</div>
        <?php } ?>
      </div>
    </div>
  </section>

  <!-- CTA Section -->
  <section style="background: linear-gradient(135deg, #4CAF50, #45a049); color: white; padding: 60px 20px; text-align: center;">
    <div class="container">
      <h2 style="font-size: 2rem; margin-bottom: 16px;">Ready to Start Cooking?</h2>
      <p style="font-size: 1.1rem; margin-bottom: 32px;">Share your recipes and earn real money today!</p>
      <a href="<?php echo !empty($_SESSION['user_id']) ? 'add-recipe.php' : 'register.php'; ?>"
        style="background: white; color: #4CAF50; padding: 12px 40px; border-radius: 4px; text-decoration: none; font-weight: bold; font-size: 1.1rem;">
        <?php echo !empty($_SESSION['user_id']) ? '➕ Add Recipe' : '📝 Create Account'; ?>
      </a>
    </div>
  </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>