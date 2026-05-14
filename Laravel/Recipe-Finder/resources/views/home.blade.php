@extends('layouts.master')

@section('content')
<main class="main-content">

  {{-- Success / Error flash message --}}
  @if (session('success'))
    <div style="background:#d4edda;color:#155724;padding:12px 20px;border-radius:8px;margin-bottom:20px;">
      {{ session('success') }}
    </div>
  @endif

  <!-- Featured Recipes Section -->
  <section id="featured" class="featured-section" aria-label="Featured recipes from around the world">

    <div class="section-header">
      <div>
        <div class="section-eyebrow">Discover</div>
        <h2 class="section-title">Featured Recipes</h2>
      </div>
    </div>

    <p class="section-desc">
      Browse handpicked recipes from around the world,
      Click any card to view full details.
    </p>

    <div id="featuredGrid" class="featured-grid" aria-live="polite">
      <!-- Skeleton loaders shown while fetching -->
    </div>

  </section>

  <hr class="divider" />

  <!-- Add Recipe Section -->
  <section id="add-recipe" class="form-section" aria-label="Add a recipe">

    <div class="section-header" style="margin-bottom:28px">
      <div>
        <div class="section-eyebrow">Your Kitchen</div>
        <h2 class="section-title">Add a New Recipe</h2>
      </div>
    </div>

    <div class="form-card">

      <div class="form-card-header">
        <h2 id="formHeading">Create Recipe</h2>
        <p id="formSubheading">Fill in the details below to save your recipe to the collection.</p>
      </div>

      <div class="form-card-body">

        {{-- Server-side validation errors --}}
        @if ($errors->any())
          <div style="background:#f8d7da;color:#721c24;padding:12px 20px;border-radius:8px;margin-bottom:20px;">
            <ul style="margin:0;padding-left:18px;">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <form id="recipeForm" action="{{ route('recipes.store') }}" method="POST" enctype="multipart/form-data" novalidate>
          @csrf

          <div class="form-grid">

            <!-- Recipe Name -->
            <div class="form-group full-width">
              <label for="recipeName">Recipe Name <span class="req">*</span></label>
              <input
                type="text"
                id="recipeName"
                name="name"
                value="{{ old('name') }}"
                placeholder="e.g. Creamy Tomato Pasta, Grilled Salmon…"
                autocomplete="off"
                required
              />
              @error('name')
                <p style="color:#c00;font-size:.85rem;margin-top:4px;">{{ $message }}</p>
              @enderror
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

              {{-- Hidden field — collectIngredients() fills this before submit --}}
              <input type="hidden" id="ingredientsHidden" name="ingredients" value="{{ old('ingredients') }}" />

              @error('ingredients')
                <p style="color:#c00;font-size:.85rem;margin-top:4px;">{{ $message }}</p>
              @enderror

              <p class="input-hint" style="margin-top:8px">Start typing to search ingredients.</p>
            </div>

            <!-- Instructions -->
            <div class="form-group full-width">
              <label for="instructions">Instructions <span class="req">*</span></label>
              <textarea
                id="instructions"
                name="instructions"
                rows="6"
                placeholder="Write the preparation steps here…"
              >{{ old('instructions') }}</textarea>
              @error('instructions')
                <p style="color:#c00;font-size:.85rem;margin-top:4px;">{{ $message }}</p>
              @enderror
            </div>

            <!-- Image Upload -->
            <div class="form-group full-width">
              <label for="recipeImage">Recipe Image</label>

              <div class="upload-zone" id="uploadZone">
                <input
                  type="file"
                  id="recipeImage"
                  name="image_path"
                  accept="image/*"
                  onchange="previewImage(this)"
                />
                <div class="upload-icon">📷</div>
                <p><strong>Click to upload</strong> or drag &amp; drop</p>
                <p style="font-size:.78rem;margin-top:4px">JPG, PNG, WEBP — max 5 MB</p>
                <div class="upload-filename" id="uploadFilename"></div>
              </div>

              <div class="image-preview-wrap" id="imagePreviewWrap">
                <img id="imagePreview" src="" alt="Recipe preview" />
              </div>

              @error('image_path')
                <p style="color:#c00;font-size:.85rem;margin-top:4px;">{{ $message }}</p>
              @enderror
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
              <button type="submit" class="btn btn-primary">
                <span id="submitBtnText">Save Recipe</span>
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

  <!-- My Recipes Section -->
  <section id="recipes-list" class="recipes-section" aria-label="My saved recipes">

    <div class="section-header">
      <div>
        <div class="section-eyebrow">Your Collection</div>
        <h2 class="section-title">My Recipes</h2>
      </div>
      <span class="section-count">{{ $recipes->count() }} {{ Str::plural('recipe', $recipes->count()) }}</span>
    </div>

    <div class="recipes-grid" id="recipesGrid">
      @forelse ($recipes as $recipe)
        <article class="recipe-card">
          @if ($recipe->image_path)
            <img class="recipe-card-image" src="{{ Storage::url($recipe->image_path) }}" alt="{{ $recipe->name }}" loading="lazy" />
          @else
            <div class="recipe-card-image-placeholder">🍽️</div>
          @endif

          <div class="recipe-card-body">
            <h3 class="recipe-card-name">{{ $recipe->name }}</h3>
            <p style="font-size:.85rem;color:var(--ink-muted);margin-bottom:14px;flex:1">
              {{ Str::limit($recipe->ingredients, 80) }}
            </p>
            <div class="recipe-card-actions">
              <a href="{{ route('recipes.show', $recipe->id) }}" class="btn btn-outline btn-sm">View</a>
              <a href="{{ route('recipes.edit', $recipe->id) }}" class="btn btn-secondary btn-sm">Edit</a>
              <form action="{{ route('recipes.destroy', $recipe->id) }}" method="POST"
                    onsubmit="return confirm('Delete this recipe?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
              </form>
            </div>
          </div>
        </article>
      @empty
        <div class="empty-state">
          <div class="empty-state-visual">🍽️</div>
          <h3>Your Recipe Book is Empty</h3>
          <p>You haven't saved any recipes yet. Use the form above to create your first one.</p>
        </div>
      @endforelse
    </div>

  </section>

</main>

<script>
// Simple image preview for the upload zone (no PHP upload needed in Laravel)
function previewImage(input) {
  const file = input.files[0];
  if (!file) return;
  const preview = document.getElementById('imagePreview');
  const previewWrap = document.getElementById('imagePreviewWrap');
  const filename = document.getElementById('uploadFilename');
  const reader = new FileReader();
  reader.onload = e => {
    if (preview) preview.src = e.target.result;
    if (previewWrap) previewWrap.style.display = 'block';
  };
  reader.readAsDataURL(file);
  if (filename) { filename.textContent = file.name; filename.style.display = 'block'; }
}

// Collect ingredient rows into the hidden field before form submits
document.getElementById('recipeForm')?.addEventListener('submit', function() {
  const rows = document.querySelectorAll('#ingredientsList .ingredient-row');
  const ingredients = [];
  rows.forEach(row => {
    const name = row.querySelector('.ingredient-name-input')?.value.trim();
    const grams = row.querySelector('.ingredient-grams-input')?.value.trim();
    if (name) ingredients.push({ name, grams: grams || '0' });
  });
  const hidden = document.getElementById('ingredientsHidden');
  if (hidden) hidden.value = JSON.stringify(ingredients);
});

function resetForm() {
  document.getElementById('recipeForm')?.reset();
  const previewWrap = document.getElementById('imagePreviewWrap');
  const preview = document.getElementById('imagePreview');
  const filename = document.getElementById('uploadFilename');
  if (previewWrap) previewWrap.style.display = 'none';
  if (preview) preview.src = '';
  if (filename) { filename.textContent = ''; filename.style.display = 'none'; }
  const list = document.getElementById('ingredientsList');
  if (list) { list.innerHTML = ''; addIngredientRow(); }
}
</script>

@endsection
