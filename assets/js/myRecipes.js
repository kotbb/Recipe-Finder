'use strict';

const RECIPE_NAME_MAX = 255;
const RECIPE_TEXT_MAX = 65535;
const RECIPE_IMAGE_PATH_MAX = 500;
const GRAM_MAX = 99999;

const UPLOAD_MAX_BYTES = 5 * 1024 * 1024;
const UPLOAD_ALLOWED_EXT = new Set(['jpg', 'jpeg', 'png', 'gif', 'webp']);

let uploadedImagePath = null;

function setUploadedImagePath(path) {
  uploadedImagePath = path || null;
}

document.addEventListener('DOMContentLoaded', () => {
  loadMyRecipes();
  bindForm();
});

function validateRecipeClient(name, instructions, ingredientsArr, imagePath) {
  if (!name) return { ok: false, message: 'Recipe name is required.' };

  if (name.length > RECIPE_NAME_MAX) {
    return { ok: false, message: `Recipe name must be at most ${RECIPE_NAME_MAX} characters.` };
  }

  if (!instructions) return { ok: false, message: 'Instructions are required.' };

  if (instructions.length > RECIPE_TEXT_MAX) {
    return { ok: false, message: 'Instructions are too long for the server limit.' };
  }

  if (!ingredientsArr.length) {
    return { ok: false, message: 'Add at least one ingredient.' };
  }

  for (const ing of ingredientsArr) {
    const n = ing.name != null ? String(ing.name).trim() : '';

    if (!n) {
      return { ok: false, message: 'Each ingredient must have a non-empty name.' };
    }

    const g = ing.grams != null ? String(ing.grams).trim() : '';

    if (g !== '' && (Number.isNaN(Number(g)) || Number(g) < 0 || Number(g) > GRAM_MAX)) {
      return { ok: false, message: `Gram amount must be between 0 and ${GRAM_MAX}.` };
    }
  }

  const ingJson = JSON.stringify(ingredientsArr);

  if (ingJson.length > RECIPE_TEXT_MAX) {
    return { ok: false, message: 'Ingredients data is too long for the server limit.' };
  }

  const path = imagePath != null ? String(imagePath) : '';

  if (path.length > RECIPE_IMAGE_PATH_MAX) {
    return { ok: false, message: `Image path must be at most ${RECIPE_IMAGE_PATH_MAX} characters.` };
  }

  return { ok: true };
}

async function loadMyRecipes() {
  const grid = document.getElementById('recipesGrid');
  if (!grid) return;

  try {
    const list = await fetchMyRecipes();
    updateRecipeStats(list);

    if (!list.length) {
      grid.innerHTML = `
        <div class="empty-state">
          <div class="empty-state-visual">🍽️</div>
          <h3>Your Recipe Book is Empty</h3>
          <p>You haven't saved any recipes yet. Click the button below to create your first one.</p>
          <button type="button" class="btn btn-gold" onclick="showPage('add-recipe')">
            Create My First Recipe
          </button>
        </div>`;
      return;
    }

    grid.innerHTML = list.map(r => myRecipeCardHTML(r)).join('');
  } catch (err) {
    updateRecipeStats([]);
    grid.innerHTML = `<p style="padding:20px;color:var(--ink-soft)">⚠️ Could not load your recipes. ${escHtml(err.message)}</p>`;
  }
}

async function deleteRecipe(id) {
  if (!confirm('Delete this recipe? This cannot be undone.')) return;

  try {
    await deleteMyRecipe(id);
    showToast('Recipe deleted.', 'info');
    loadMyRecipes();
  } catch (err) {
    showToast(`${err.message}`, 'error');
  }
}

function populateEditForm(safeJson) {
  let recipe;

  try {
    recipe = JSON.parse(safeJson);
  } catch {
    return;
  }

  const editIndex = document.getElementById('editIndex');
  const recipeName = document.getElementById('recipeName');
  const instructions = document.getElementById('instructions');

  if (editIndex) editIndex.value = recipe.id;
  if (recipeName) recipeName.value = recipe.name || '';
  if (instructions) instructions.value = recipe.instructions || '';

  const heading = document.getElementById('formHeading');
  const subheading = document.getElementById('formSubheading');
  const btnText = document.getElementById('submitBtnText');

  if (heading) heading.textContent = 'Edit Recipe';
  if (subheading) subheading.textContent = 'Update the details below and save your changes.';
  if (btnText) btnText.textContent = 'Update Recipe';

  const list = document.getElementById('ingredientsList');

  if (list) {
    list.innerHTML = '';

    let ingredients = [];

    try {
      ingredients = JSON.parse(recipe.ingredients);
    } catch {
      ingredients = [];
    }

    if (!ingredients.length) {
      addIngredientRow();
    } else {
      ingredients.forEach(ing => {
        addIngredientRow();

        const rows = list.querySelectorAll('.ingredient-row');
        const row = rows[rows.length - 1];

        if (!row) return;

        const nameInput = row.querySelector('.ingredient-name-input');
        const gramsInput = row.querySelector('.ingredient-grams-input');

        if (nameInput) nameInput.value = ing.name || '';
        if (gramsInput) gramsInput.value = ing.grams || '';
      });
    }
  }

  setUploadedImagePath(recipe.image_path || null);

  if (recipe.image_path) {
    const preview = document.getElementById('imagePreview');
    const previewWrap = document.getElementById('imagePreviewWrap');
    const filename = document.getElementById('uploadFilename');

    if (preview) preview.src = recipe.image_path;
    if (previewWrap) previewWrap.style.display = 'block';
    if (filename) filename.textContent = recipe.image_path.split('/').pop();
  }

  showPage('add-recipe');
}

function updateRecipeStats(recipes) {
  const count = document.getElementById('recipeCount');

  if (count) {
    count.textContent = `${recipes.length} recipe${recipes.length !== 1 ? 's' : ''}`;
  }

  const withImage = recipes.filter(r => Boolean(r.image_path)).length;

  const totalIngredients = recipes.reduce((total, recipe) => {
    try {
      const ingList = JSON.parse(recipe.ingredients || '[]');
      return total + (Array.isArray(ingList) ? ingList.length : 0);
    } catch {
      return total;
    }
  }, 0);

  const imageCountEl = document.getElementById('statsRecipesWithImages');
  const ingredientsEl = document.getElementById('statsTotalIngredients');

  if (imageCountEl) imageCountEl.textContent = String(withImage);
  if (ingredientsEl) ingredientsEl.textContent = String(totalIngredients);
}

function myRecipeCardHTML(r) {
  const img = r.image_path
    ? `<img class="recipe-card-image" src="${escHtml(r.image_path)}" alt="${escHtml(r.name)}" loading="lazy" />`
    : `<div class="recipe-card-image-placeholder">🍽️<span class="recipe-card-badge">No photo</span></div>`;

  let ingPreview = '';

  try {
    const parsed = JSON.parse(r.ingredients);
    ingPreview = parsed.slice(0, 3).map(i => i.name || i).join(', ');
    if (parsed.length > 3) ingPreview += ` +${parsed.length - 3} more`;
  } catch {
    ingPreview = String(r.ingredients).slice(0, 80);
  }

  const safeJson = escAttr(JSON.stringify(r));

  return `
    <article class="recipe-card">
      <div style="position:relative">${img}</div>
      <div class="recipe-card-body">
        <h3 class="recipe-card-name">${escHtml(r.name)}</h3>
        <p style="font-size:.85rem;color:var(--ink-muted);margin-bottom:14px;flex:1">
          ${escHtml(ingPreview)}
        </p>
        <div class="recipe-card-actions">
          <button class="btn btn-outline btn-sm" onclick="openMyRecipeModal('${safeJson}')">View</button>
          <button class="btn btn-secondary btn-sm" onclick="populateEditForm('${safeJson}')">Edit</button>
          <button class="btn btn-danger btn-sm" onclick="deleteRecipe(${r.id})">Delete</button>
        </div>
      </div>
    </article>`;
}

function bindForm() {
  const form = document.getElementById('recipeForm');
  if (!form) return;

  form.addEventListener('submit', async e => {
    e.preventDefault();
    await saveRecipe();
  });
}

async function saveRecipe() {
  const name = document.getElementById('recipeName')?.value.trim();
  const instructions = document.getElementById('instructions')?.value.trim();
  const editId = document.getElementById('editIndex')?.value;

  const ingredients = collectIngredients();

  const check = validateRecipeClient(name, instructions, ingredients, uploadedImagePath);

  if (!check.ok) {
    showToast(check.message, 'error');
    return;
  }

  const payload = {
    name,
    ingredients: JSON.stringify(ingredients),
    instructions,
    image_path: uploadedImagePath || ''
  };

  const isEdit = editId !== '' && editId != null;

  const btnText = document.getElementById('submitBtnText');

  if (btnText) btnText.textContent = 'Saving...';

  try {
    if (isEdit) {
      await updateMyRecipe(editId, payload);
    } else {
      await addMyRecipe(payload);
    }

    showToast(isEdit ? 'Recipe updated!' : 'Recipe saved!', 'success');
    resetForm();
    loadMyRecipes();
    showPage('recipes-list');
  } catch (err) {
    showToast(`${err.message}`, 'error');
  } finally {
    if (btnText) btnText.textContent = isEdit ? 'Update Recipe' : 'Save Recipe';
  }
}

function collectIngredients() {
  const result = [];

  document.querySelectorAll('#ingredientsList .ingredient-row').forEach(row => {
    const name = row.querySelector('.ingredient-name-input')?.value.trim();
    const grams = row.querySelector('.ingredient-grams-input')?.value.trim();

    if (name) {
      result.push({
        name,
        grams: grams || '0'
      });
    }
  });

  return result;
}

function resetForm() {
  document.getElementById('recipeForm')?.reset();

  const editIndex = document.getElementById('editIndex');
  if (editIndex) editIndex.value = '';

  setUploadedImagePath(null);

  const heading = document.getElementById('formHeading');
  const subheading = document.getElementById('formSubheading');
  const btnText = document.getElementById('submitBtnText');

  if (heading) heading.textContent = 'Create Recipe';
  if (subheading) subheading.textContent = 'Fill in the details below to save your recipe to the collection.';
  if (btnText) btnText.textContent = 'Save Recipe';

  const previewWrap = document.getElementById('imagePreviewWrap');
  const preview = document.getElementById('imagePreview');
  const filename = document.getElementById('uploadFilename');

  if (previewWrap) previewWrap.style.display = 'none';
  if (preview) preview.src = '';
  if (filename) filename.textContent = '';

  const list = document.getElementById('ingredientsList');

  if (list) {
    list.innerHTML = '';
    addIngredientRow();
  }
}

async function handleImageUpload(input) {
  const file = input.files[0];

  if (!file) return;

  const ext = (file.name.includes('.') ? file.name.split('.').pop() : '').toLowerCase();

  if (!UPLOAD_ALLOWED_EXT.has(ext)) {
    showToast('Only JPG, JPEG, PNG, GIF and WEBP images are allowed.', 'error');
    input.value = '';
    return;
  }

  if (file.size > UPLOAD_MAX_BYTES) {
    showToast('Image must be at most 5 MB.', 'error');
    input.value = '';
    return;
  }

  const filenameEl = document.getElementById('uploadFilename');
  const previewEl = document.getElementById('imagePreview');
  const previewWrap = document.getElementById('imagePreviewWrap');

  const reader = new FileReader();

  reader.onload = e => {
    if (previewEl) previewEl.src = e.target.result;
    if (previewWrap) previewWrap.style.display = 'block';
  };

  reader.readAsDataURL(file);

  if (filenameEl) {
    filenameEl.textContent = `Uploading ${file.name}...`;
    filenameEl.style.display = 'block';
  }

  try {
    const filePath = await uploadRecipeImage(file);
    setUploadedImagePath(filePath);
    if (previewEl) previewEl.src = uploadedImagePath;
    if (filenameEl) filenameEl.textContent = file.name;

    showToast('Image uploaded!', 'success');
  } catch (err) {
    setUploadedImagePath(null);
    showToast(`Image upload failed: ${err.message}`, 'error');
    if (filenameEl) filenameEl.textContent = `${file.name} (preview only)`;
  }
}

function openMyRecipeModal(safeJson) {
  let r;

  try {
    r = JSON.parse(safeJson);
  } catch {
    return;
  }

  let ingredients = '';

  try {
    const parsed = JSON.parse(r.ingredients);

    ingredients = parsed.map(i =>
      `<span class="modal-ingredient-tag">${escHtml(i.name)}${i.grams && i.grams !== '0' ? ` <small>${i.grams}g</small>` : ''}</span>`
    ).join('');
  } catch {
    ingredients = `<span class="modal-ingredient-tag">${escHtml(r.ingredients)}</span>`;
  }

  fillAndOpenModal({
    imageSrc: r.image_path || null,
    imageAlt: r.name,
    title: r.name,
    ingredients,
    instructions: r.instructions,
    editLabel: 'Edit',
    onEdit: () => {
      closeModal();
      populateEditForm(safeJson);
    }
  });
}

function fillAndOpenModal({ imageSrc, imageAlt, title, ingredients, instructions, editLabel, onEdit }) {
  const modal = document.getElementById('viewModal');
  const imageWrap = document.getElementById('modalImage');
  const titleEl = document.getElementById('modalTitle');
  const ingredientsEl = document.getElementById('modalIngredients');
  const instructionsEl = document.getElementById('modalInstructions');
  const editBtn = document.getElementById('modalEditBtn');

  if (!modal) return;

  imageWrap.innerHTML = imageSrc
    ? `<img class="modal-image" src="${escHtml(imageSrc)}" alt="${escHtml(imageAlt)}" />`
    : `<div class="modal-image-placeholder">🍽️</div>`;

  titleEl.textContent = title;
  ingredientsEl.innerHTML = ingredients;
  instructionsEl.textContent = instructions;

  if (editBtn) {
    if (editLabel && onEdit) {
      editBtn.style.display = '';
      editBtn.textContent = editLabel;
      editBtn.onclick = onEdit;
    } else {
      editBtn.style.display = 'none';
    }
  }

  modal.classList.add('open');
}

function closeModal(e) {
  if (!e || e.target === document.getElementById('viewModal')) {
    document.getElementById('viewModal')?.classList.remove('open');
  }
}

function showToast(message, type = 'info') {
  const container = document.getElementById('toastContainer');

  if (!container) {
    alert(message);
    return;
  }

  const icons = {
    success: '✔',
    error: '✖',
    info: '✦'
  };

  const toast = document.createElement('div');

  toast.className = `toast ${type}`;
  toast.innerHTML = `<span>${icons[type] || '✦'}</span> ${escHtml(message)}`;

  container.appendChild(toast);

  setTimeout(() => {
    toast.style.animation = 'toastOut .3s forwards';
    setTimeout(() => toast.remove(), 300);
  }, 3200);
}

function escHtml(str) {
  return String(str ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

function escAttr(str) {
  return String(str ?? '')
    .replace(/\\/g, '\\\\')
    .replace(/'/g, "\\'")
    .replace(/"/g, '&quot;');
}

window.loadMyRecipes = loadMyRecipes;
window.deleteRecipe = deleteRecipe;
window.populateEditForm = populateEditForm;
window.updateRecipeStats = updateRecipeStats;
window.openMyRecipeModal = openMyRecipeModal;
window.closeModal = closeModal;
window.resetForm = resetForm;
window.handleImageUpload = handleImageUpload;
window.showToast = showToast;
window.setUploadedImagePath = setUploadedImagePath;
window.fillAndOpenModal = fillAndOpenModal;
window.escHtml = escHtml;
window.escAttr = escAttr;