@extends('layouts.master')

@section('content')
<main class="main-content">

  <section class="form-section" aria-label="Edit recipe">

    <div class="section-header" style="margin-bottom:28px">
      <div>
        <div class="section-eyebrow">Your Collection</div>
        <h2 class="section-title">Edit Recipe</h2>
      </div>
      <a href="{{ route('home') }}" class="btn btn-secondary btn-sm">← Back</a>
    </div>

    <div class="form-card">

      <div class="form-card-header">
        <h2>{{ $recipe->name }}</h2>
        <p>Update the details below and save your changes.</p>
      </div>

      <div class="form-card-body">

        {{-- Validation errors --}}
        @if ($errors->any())
          <div style="background:#f8d7da;color:#721c24;padding:12px 20px;border-radius:8px;margin-bottom:20px;">
            <ul style="margin:0;padding-left:18px;">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <form action="{{ route('recipes.update', $recipe->id) }}" method="POST" enctype="multipart/form-data" novalidate>
          @csrf
          @method('PUT')

          <div class="form-grid">

            {{-- Recipe Name --}}
            <div class="form-group full-width">
              <label for="recipeName">Recipe Name <span class="req">*</span></label>
              <input
                type="text"
                id="recipeName"
                name="name"
                value="{{ old('name', $recipe->name) }}"
                placeholder="e.g. Creamy Tomato Pasta…"
                autocomplete="off"
                required
              />
              @error('name')
                <p style="color:#c00;font-size:.85rem;margin-top:4px;">{{ $message }}</p>
              @enderror
            </div>

            {{-- Ingredients --}}
            {{-- Normalise the value: old() is already a JSON string; $recipe->ingredients  --}}
            {{-- may be an array (cast) or a double-encoded string (legacy bad saves).      --}}
            @php
              if (old('ingredients')) {
                  $ingredientsJson = old('ingredients');
              } else {
                  $ings = $recipe->ingredients;
                  // Handle legacy double-encoded strings in the DB
                  if (is_string($ings)) $ings = json_decode($ings, true);
                  if (is_string($ings)) $ings = json_decode($ings, true);
                  if (!is_array($ings)) $ings = [];
                  $ingredientsJson = json_encode($ings);
              }
            @endphp
            <div class="form-group full-width ingredients-section">
              <div class="ingredients-header">
                <label>Ingredients <span class="req">*</span></label>
              </div>

              <div class="ingredients-list" id="ingredientsList" data-managed="true"></div>

              <button type="button" class="add-ingredient-trigger" onclick="addIngredientRow()">
                <span>＋</span> Add another ingredient
              </button>

              <input type="hidden" id="ingredientsHidden" name="ingredients"
                value="{{ $ingredientsJson }}" />

              @error('ingredients')
                <p style="color:#c00;font-size:.85rem;margin-top:4px;">{{ $message }}</p>
              @enderror
            </div>

            {{-- Instructions --}}
            <div class="form-group full-width">
              <label for="instructions">Instructions <span class="req">*</span></label>
              <textarea
                id="instructions"
                name="instructions"
                rows="6"
                placeholder="Write the preparation steps here…"
              >{{ old('instructions', $recipe->instructions) }}</textarea>
              @error('instructions')
                <p style="color:#c00;font-size:.85rem;margin-top:4px;">{{ $message }}</p>
              @enderror
            </div>

            {{-- Current Image --}}
            @if ($recipe->image_path)
              <div class="form-group full-width">
                <label>Current Image</label>
                <img
                  src="{{ Storage::url($recipe->image_path) }}"
                  alt="{{ $recipe->name }}"
                  style="max-width:260px;border-radius:10px;display:block;margin-top:8px;"
                />
              </div>
            @endif

            {{-- New Image Upload --}}
            <div class="form-group full-width">
              <label for="recipeImage">{{ $recipe->image_path ? 'Replace Image' : 'Recipe Image' }}</label>
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

            {{-- Form Actions --}}
            <div class="form-actions">
              <button type="submit" class="btn btn-primary">Save Changes</button>
              <a href="{{ route('home') }}" class="btn btn-secondary">Cancel</a>
            </div>

          </div>
        </form>
      </div>
    </div>

  </section>

</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const list   = document.getElementById('ingredientsList');
  const hidden = document.getElementById('ingredientsHidden');
  if (!list || !hidden) return;

  function makeRow(name, grams) {
    const div = document.createElement('div');
    div.className = 'ingredient-row';
    div.innerHTML = `
      <div class="ingredient-input-wrap">
        <input type="text" class="ingredient-name-input" placeholder="Enter ingredient..." autocomplete="off" value="${name || ''}" />
      </div>
      <div class="gram-input">
        <input type="number" class="ingredient-grams-input" placeholder="0" min="0" max="99999" value="${grams || ''}" />
        <span class="gram-unit">g</span>
      </div>
      <button type="button" class="btn-icon btn-add-row" title="Add row">＋</button>
      <button type="button" class="btn-icon btn-delete-row" title="Remove">✕</button>
    `;
    return div;
  }

  window.addIngredientRow = function () {
    const row = makeRow('', '');
    list.appendChild(row);
    row.querySelector('.ingredient-name-input')?.focus();
  };

  list.addEventListener('click', function (e) {
    if (e.target.closest('.btn-add-row')) {
      window.addIngredientRow();
    }
    if (e.target.closest('.btn-delete-row')) {
      const row = e.target.closest('.ingredient-row');
      if (list.children.length > 1) row?.remove();
    }
  });

  let saved = [];
  try {
    saved = JSON.parse(hidden.value);
    // Handle legacy double-encoded strings that may still be in the DB
    if (typeof saved === 'string') saved = JSON.parse(saved);
  } catch (e) { console.log('parse error', e); }

  list.innerHTML = '';
  if (Array.isArray(saved) && saved.length > 0) {
    saved.forEach(item => list.appendChild(makeRow(item.name, item.grams)));
  } else {
    list.appendChild(makeRow('', ''));
  }

  document.querySelector('form')?.addEventListener('submit', function () {
    const ingredients = [];
    list.querySelectorAll('.ingredient-row').forEach(row => {
      const name  = row.querySelector('.ingredient-name-input')?.value.trim();
      const grams = row.querySelector('.ingredient-grams-input')?.value.trim();
      if (name) ingredients.push({ name, grams: grams || '0' });
    });
    if (ingredients.length > 0) {
      hidden.value = JSON.stringify(ingredients);
    }
  });
});

function previewImage(input) {
  const file = input.files[0];
  if (!file) return;
  const preview     = document.getElementById('imagePreview');
  const previewWrap = document.getElementById('imagePreviewWrap');
  const filename    = document.getElementById('uploadFilename');
  const reader = new FileReader();
  reader.onload = e => {
    if (preview) preview.src = e.target.result;
    if (previewWrap) previewWrap.style.display = 'block';
  };
  reader.readAsDataURL(file);
  if (filename) { filename.textContent = file.name; filename.style.display = 'block'; }
}
</script>

@endsection