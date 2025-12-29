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
  <section style=" color: white; text-align: center;">
    <div class="container">
      <h1 style="font-size: 40px; margin-bottom: 16px; color: black">🍳 Ranna Kori</h1>
    </div>
    <div class="container">
      <form method="get" action="recipes.php" style="display: flex; gap: 8px; max-width: 600px; height: 60px; margin: 0 auto 40px auto; border-radius: 10px; overflow: hidden; ">
        <input
          name="search"
          placeholder="🔍 রেসিপি বা উপকরণ দিয়ে খুঁজুন"
          value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>"
          style="
        flex: 1; 
        padding: 12px; 
        font-size: 17px; 
        border: solid 1px #959393ff; 
        border-radius: 10px;
        font-family: Arial, sans-serif;
        outline: none;
      ">
        <button type="submit" style="
      background: #FF9800; 
      color: white; 
      padding: 12px 24px; 
      max-width: 150px;
      border: none;
      border-radius: 10px; 
      cursor: pointer; 
      /* font-weight: bold; */
      font-size: 18px;
    ">
          রেসিপি খুঁজুন
        </button>
      </form>
    </div>
  </section>

  <!-- Featured Recipes Section -->
  <section style=" margin: 40px 0; background: #f5f5f5;">
    <div class="container" style="width: 100%; margin: 0 auto;">
      <h2 style="font-size: 1.5rem; margin-bottom: 40px; text-align: left;">Trending Keywords</h2>

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
                    style="width: 100%; height: 150px; object-fit: cover;">
                <?php else: ?>
                  <div style="width: 100%; height: 150px; background: #ddd; display: flex; align-items: center; justify-content: center; font-size: 3rem;">
                    🍳
                  </div>
                <?php endif; ?>

                <!-- Recipe Info -->
                <div style="padding: 10px;">
                  <h3 style="margin: 0 0 8px 0;">
                    <a href="recipe-details.php?id=<?php echo (int)$recipe['id']; ?>" style="text-decoration: none; color: #333;">
                      <?php echo htmlspecialchars($recipe['title']); ?>
                    </a>
                  </h3>

                  <p style="color: #666; font-size: 0.9rem; margin: 0 0 8px 0;">
                    By <strong><?php echo htmlspecialchars($recipe['author_name']); ?></strong>
                  </p>


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
  <section style="border-radius: 8px; padding: 20px 60px; padding-bottom: 60px; background: white;">
    <div class="container" style=" margin: 0 auto;">
      <h2 style="font-size: 2rem; margin-bottom: 20px; text-align: center;">How to Earn Points</h2>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
        <div style="background: #f0f8f0; padding: 30px; border-radius: 8px; text-align: center; border: 2px solid #14f11bff;">
          <div style="font-size: 2.5rem; margin-bottom: 12px;">📝</div>
          <h3 style="margin-bottom: 8px;">Post Recipe</h3>
          <p style="color: #666; font-size: 1.2rem; font-weight: bold; margin: 0;">+50 Points</p>
        </div>

        <div style="background: #f0f8f0; padding: 30px; border-radius: 8px; text-align: center; border: 2px solid #14f11bff;">
          <div style="font-size: 2.5rem; margin-bottom: 12px;">💬</div>
          <h3 style="margin-bottom: 8px;">Write Review</h3>
          <p style="color: #666; font-size: 1.2rem; font-weight: bold; margin: 0;">+10 Points</p>
        </div>

        <div style="background: #f0f8f0; padding: 30px; border-radius: 8px; text-align: center; border: 2px solid #14f11bff;">
          <div style="font-size: 2.5rem; margin-bottom: 12px;">❤️</div>
          <h3 style="margin-bottom: 8px;">Get Liked</h3>
          <p style="color: #666; font-size: 1.2rem; font-weight: bold; margin: 0;">+1 Point</p>
        </div>

        <div style="background: #f0f8f0; padding: 30px; border-radius: 8px; text-align: center; border: 2px solid #14f11bff;">
          <div style="font-size: 2.5rem; margin-bottom: 12px;">💳</div>
          <h3 style="margin-bottom: 8px;">Redeem</h3>
          <p style="color: #666; font-size: 1.2rem; font-weight: bold; margin: 0;">100 Points = 10 TK</p>
        </div>
      </div>
    </div>
  </section>

  

  <!-- CTA Section -->
  <section style="padding: 40px 0px; margin: 40px 0; background: #4CAF50; color: white; text-align: center; border-radius: 8px;">
    <div class="container">
      <h2 style="font-size: 2rem; margin-bottom: 5px; padding: 10px;">Ready to Start Cooking?</h2>
      <p style="font-size: 1.1rem; margin-bottom: 20px;">Share your recipes and earn real money today!</p>
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
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>