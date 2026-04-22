<?php include 'includes/header.php'; ?>

<main class="main-content">

  <!-- ============================================================
       FEATURED RECIPES — Powered by TheMealDB free API
       Endpoint: https://www.themealdb.com/api/json/v1/1/filter.php?c=<Category>
       ============================================================ -->
  <section id="featured" class="featured-section" aria-label="Featured recipes from around the world">

    <div class="section-header">
      <div>
        <div class="section-eyebrow">Discover</div>
        <h2 class="section-title">Featured Recipes</h2>
      </div>
    </div>

    <p class="section-desc">
      Browse handpicked recipes from around the world, powered by <strong>TheMealDB</strong>.
      Click any card to view full details.
    </p>

    <!-- Category filter pills -->
    <div class="featured-filters" id="featuredFilters" role="group" aria-label="Filter by category">
      <button class="filter-btn active" data-cat="Chicken">🍗 Chicken</button>
      <button class="filter-btn" data-cat="Beef">🥩 Beef</button>
      <button class="filter-btn" data-cat="Seafood">🐟 Seafood</button>
      <button class="filter-btn" data-cat="Vegetarian">🥦 Vegetarian</button>
      <button class="filter-btn" data-cat="Pasta">🍝 Pasta</button>
      <button class="filter-btn" data-cat="Dessert">🍮 Dessert</button>
    </div>

    <!-- Grid — filled by JS -->
    <div id="featuredGrid" class="featured-grid" aria-live="polite">
      <!-- Skeleton loaders shown while fetching -->
    </div>

  </section>

  <hr class="divider" />

  <!-- ============================================================
       ADD / EDIT RECIPE FORM
       ============================================================ -->
  <section id="add-recipe" class="form-section" aria-label="Add or edit a recipe">

    <div class="section-header" style="margin-bottom:28px">
      <div>
        <div class="section-eyebrow">Your Kitchen</div>
        <h2 class="section-title">Add a New Recipe</h2>
      </div>
    </div>

    <div class="form-card">

      <div class="form-card-header">
        <h2 id="formHeading">✦ Create Recipe</h2>
        <p id="formSubheading">Fill in the details below to save your recipe to the collection.</p>
      </div>

      <div class="form-card-body">
        <form id="recipeForm" novalidate>
          <input type="hidden" id="editIndex" value="" />
          <div class="form-grid">

            <!-- Recipe Name -->
            <div class="form-group full-width">
              <label for="recipeName">Recipe Name <span class="req">*</span></label>
              <input
                type="text" id="recipeName" name="recipe_name"
                placeholder="e.g. Creamy Tomato Pasta, Grilled Salmon…"
                autocomplete="off" required
              />
            </div>

            <!-- Ingredients -->
            <div class="form-group full-width ingredients-section">
              <div class="ingredients-header">
                <label>Ingredients <span class="req">*</span></label>
              </div>
              <div class="ingredients-list" id="ingredientsList"></div>
              <button type="button" class="add-ingredient-trigger" onclick="addIngredientRow()">
                <span>＋</span> Add another ingredient
              </button>
              <p class="input-hint" style="margin-top:8px">Start typing to search ingredients.</p>
            </div>

            <!-- Instructions -->
            <div class="form-group full-width">
              <label for="instructions">Instructions <span class="req">*</span></label>
              <textarea
                id="instructions" name="instructions" rows="6"
                placeholder="Write the preparation steps here… e.g.&#10;1. Preheat the oven to 180°C.&#10;2. Dice the onions and sauté in olive oil…"
              ></textarea>
            </div>

            <!-- Image Upload -->
            <div class="form-group full-width">
              <label for="recipeImage">Recipe Image</label>
              <div class="upload-zone" id="uploadZone">
                <input type="file" id="recipeImage" name="recipe_image" accept="image/*" onchange="handleImageUpload(this)" />
                <div class="upload-icon">📷</div>
                <p><strong>Click to upload</strong> or drag &amp; drop</p>
                <p style="font-size:.78rem;margin-top:4px">JPG, PNG, WEBP — max 5 MB</p>
                <div class="upload-filename" id="uploadFilename"></div>
              </div>
              <div class="image-preview-wrap" id="imagePreviewWrap">
                <img id="imagePreview" src="" alt="Recipe preview" />
              </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
              <button type="submit" class="btn btn-primary">
                <span>✦</span> <span id="submitBtnText">Save Recipe</span>
              </button>
              <button type="button" class="btn btn-secondary" onclick="resetForm()">
                Clear Form
              </button>
            </div>

          </div>
        </form>
      </div>

    </div>
  </section>

  <hr class="divider" />

  <!-- ============================================================
       MY RECIPES — empty until user adds
       ============================================================ -->
  <section id="recipes-list" class="recipes-section" aria-label="My saved recipes">

    <div class="section-header">
      <div>
        <div class="section-eyebrow">Your Collection</div>
        <h2 class="section-title">My Recipes</h2>
      </div>
      <span class="section-count" id="recipeCount">0 recipes</span>
    </div>

    <div class="recipes-grid" id="recipesGrid">
      <!-- Populated by JS — starts empty -->
    </div>

  </section>

</main>

<?php include 'includes/footer.php'; ?>