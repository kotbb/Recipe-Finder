/* =========================================
   RECIPE MANAGER — app.js
   Vanilla JS — no frameworks
   TheMealDB API: https://www.themealdb.com/api/json/v1/1/
   ========================================= */

'use strict';

/* ── Ingredient suggestions DB ── */
const INGREDIENTS_DB = [
  'Tomato','Onion','Garlic','Chicken','Beef','Salt','Black Pepper',
  'Olive Oil','Butter','Cheese','Milk','Egg','Flour','Rice','Pasta',
  'Basil','Carrot','Potato','Lemon','Lime','Ginger','Cumin','Paprika',
  'Thyme','Rosemary','Oregano','Coriander','Parsley','Chilli','Spinach',
  'Mushroom','Zucchini','Bell Pepper','Broccoli','Cauliflower','Cream',
  'Yoghurt','Honey','Soy Sauce','Vinegar','Baking Powder','Baking Soda',
  'Sugar','Brown Sugar','Vanilla','Cinnamon','Nutmeg','Breadcrumbs',
  'Salmon','Tuna','Shrimp','Cod','Bacon','Sausage','Lamb','Turkey',
  'Avocado','Sweet Potato','Pumpkin','Celery','Leek','Asparagus',
  'Coconut Milk','Tomato Paste','Vegetable Stock','Chicken Stock'
];

const STORAGE_KEY = 'recipe_manager_v2';
const MEALDB_BASE = 'https://www.themealdb.com/api/json/v1/1';

/* ── State ── */
let recipes         = loadRecipes();     // starts EMPTY by default
let activeCategory  = 'Chicken';
let activeSugIdx    = -1;
let rowCounter      = 0;

/* ── DOM Ready ── */
document.addEventListener('DOMContentLoaded', () => {
  addIngredientRow();
  renderMyRecipes();
  updateHeroCount();
  initFeaturedFilters();
  loadFeaturedRecipes(activeCategory);
  setupForm();
  setupDragDrop();
});

/* =========================================
   LOCAL STORAGE — starts empty
   ========================================= */
function loadRecipes() {
  try {
    const saved = localStorage.getItem(STORAGE_KEY);
    // Return empty array if nothing saved yet — no sample data
    return saved ? JSON.parse(saved) : [];
  } catch { return []; }
}

function saveRecipes() {
  localStorage.setItem(STORAGE_KEY, JSON.stringify(recipes));
}

/* =========================================
   FEATURED RECIPES — TheMealDB API
   ========================================= */
function initFeaturedFilters() {
  document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
      document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
      this.classList.add('active');
      activeCategory = this.dataset.cat;
      loadFeaturedRecipes(activeCategory);
    });
  });
}

async function loadFeaturedRecipes(category) {
  const grid = document.getElementById('featuredGrid');

  // Show skeletons while loading
  grid.innerHTML = buildSkeletons(8);

  try {
    // Step 1: Get meal list for category
    const listRes  = await fetch(`${MEALDB_BASE}/filter.php?c=${encodeURIComponent(category)}`);
    if (!listRes.ok) throw new Error('Network error');
    const listData = await listRes.json();

    if (!listData.meals) throw new Error('No meals found');

    // Take first 8 results
    const meals = listData.meals.slice(0, 8);

    // Step 2: Fetch details for each meal (for category + area badges)
    const detailPromises = meals.map(m =>
      fetch(`${MEALDB_BASE}/lookup.php?i=${m.idMeal}`)
        .then(r => r.json())
        .then(d => d.meals ? d.meals[0] : null)
        .catch(() => null)
    );

    const details = await Promise.all(detailPromises);

    // Merge list thumbnails with detail data
    const enriched = meals.map((m, i) => ({
      id:           m.idMeal,
      name:         m.strMeal,
      thumb:        m.strMealThumb,
      category:     details[i]?.strCategory   || category,
      area:         details[i]?.strArea        || '',
      tags:         details[i]?.strTags        || '',
      source:       details[i]?.strSource      || '',
      youtube:      details[i]?.strYoutube     || '',
      instructions: details[i]?.strInstructions || ''
    }));

    renderFeaturedGrid(enriched);

  } catch (err) {
    grid.innerHTML = `
      <div class="api-error">
        <div style="font-size:2rem;margin-bottom:10px">⚠️</div>
        <h3 style="font-family:var(--font-display);color:var(--ink)">Could not load recipes</h3>
        <p>Please check your internet connection and try again.</p>
        <button class="btn btn-outline btn-sm" style="margin-top:16px"
          onclick="loadFeaturedRecipes('${activeCategory}')">Retry</button>
      </div>`;
  }
}

function buildSkeletons(n) {
  return Array.from({ length: n }, () => `
    <div class="skeleton-card">
      <div class="skeleton-img"></div>
      <div class="skeleton-body">
        <div class="skeleton-line med"></div>
        <div class="skeleton-line short"></div>
      </div>
    </div>`).join('');
}

function renderFeaturedGrid(meals) {
  const grid = document.getElementById('featuredGrid');

  if (!meals.length) {
    grid.innerHTML = '<div class="api-error"><p>No recipes found for this category.</p></div>';
    return;
  }

  grid.innerHTML = meals.map(m => featuredCardHTML(m)).join('');
}

function featuredCardHTML(m) {
  const tagList = m.tags ? m.tags.split(',').slice(0,2) : [];
  const tagsHTML = tagList.map(t =>
    `<span class="featured-card-tag">${escHtml(t.trim())}</span>`
  ).join('');
  const areaHTML = m.area ? `<span class="featured-card-area">🌍 ${escHtml(m.area)}</span>` : '';
  const linkHTML = m.youtube
    ? `<a class="featured-card-link" href="${escHtml(m.youtube)}" target="_blank" rel="noopener">Watch ▶</a>`
    : m.source
      ? `<a class="featured-card-link" href="${escHtml(m.source)}" target="_blank" rel="noopener">View ↗</a>`
      : '';

  return `
    <article class="featured-card" onclick="openFeaturedModal(${JSON.stringify(escHtml(JSON.stringify(m))).slice(1,-1)})">
      <div class="featured-card-img-wrap">
        <img class="featured-card-img"
             src="${escHtml(m.thumb)}/preview"
             alt="${escHtml(m.name)}"
             loading="lazy"
             onerror="this.src='${escHtml(m.thumb)}'"
        />
        <span class="featured-card-category">${escHtml(m.category)}</span>
        ${areaHTML}
      </div>
      <div class="featured-card-body">
        <h3 class="featured-card-name">${escHtml(m.name)}</h3>
        <div class="featured-card-meta">
          ${tagsHTML}
          ${linkHTML}
        </div>
      </div>
    </article>`;
}

/* Featured detail modal (reuses the existing view modal) */
function openFeaturedModal(jsonStr) {
  let m;
  try { m = JSON.parse(jsonStr); } catch { return; }

  document.getElementById('modalTitle').textContent = m.name;

  const imgWrap = document.getElementById('modalImage');
  imgWrap.innerHTML = `<img class="modal-image" src="${escHtml(m.thumb)}" alt="${escHtml(m.name)}" />`;

  // Build tag list from category + area + tags
  const tagParts = [m.category, m.area, ...(m.tags ? m.tags.split(',').map(t=>t.trim()) : [])].filter(Boolean);
  document.getElementById('modalIngredients').innerHTML = tagParts.map(t =>
    `<span class="modal-ingredient-tag">${escHtml(t)}</span>`
  ).join('');

  const instr = m.instructions || 'Visit the recipe source for full instructions.';
  document.getElementById('modalInstructions').textContent = instr.slice(0, 800) + (instr.length > 800 ? '…' : '');

  const editBtn = document.getElementById('modalEditBtn');
  if (m.youtube) {
    editBtn.textContent = 'Watch on YouTube ▶';
    editBtn.onclick = () => window.open(m.youtube, '_blank');
  } else if (m.source) {
    editBtn.textContent = 'View Full Recipe ↗';
    editBtn.onclick = () => window.open(m.source, '_blank');
  } else {
    editBtn.style.display = 'none';
  }

  document.getElementById('viewModal').classList.add('open');
}

/* =========================================
   INGREDIENT ROWS
   ========================================= */
function addIngredientRow(name = '', grams = '') {
  const id   = `row_${++rowCounter}`;
  const list = document.getElementById('ingredientsList');
  const row  = document.createElement('div');
  row.className = 'ingredient-row';
  row.id = id;

  row.innerHTML = `
    <div class="ingredient-input-wrap">
      <input type="text" class="ingredient-name-input"
        placeholder="Search ingredient…"
        value="${escHtml(name)}" autocomplete="off"
        oninput="showSuggestions(this,'${id}')"
        onblur="hideSuggestionsDelayed('${id}')"
        onkeydown="navigateSuggestions(event,'${id}')"
      />
      <div class="ingredient-suggestions" id="sug_${id}"></div>
    </div>
    <div class="gram-input">
      <input type="number" class="ingredient-grams-input"
        placeholder="0" value="${escHtml(String(grams))}" min="0" max="99999"
      />
      <span class="gram-unit">g</span>
    </div>
    <button type="button" class="btn-icon btn-add-row" onclick="addIngredientRow()" title="Add row">＋</button>
    <button type="button" class="btn-icon btn-delete-row" onclick="removeIngredientRow('${id}')" title="Remove">✕</button>
  `;

  list.appendChild(row);
  if (!name) row.querySelector('.ingredient-name-input').focus();
}

function removeIngredientRow(id) {
  const list = document.getElementById('ingredientsList');
  const row  = document.getElementById(id);
  if (!row) return;
  if (list.children.length === 1) { showToast('At least one ingredient row is required.', 'error'); return; }
  row.style.cssText = 'opacity:0;transform:translateY(-6px);transition:opacity .2s,transform .2s';
  setTimeout(() => row.remove(), 200);
}

function getIngredientRows() {
  const result = [];
  document.querySelectorAll('#ingredientsList .ingredient-row').forEach(row => {
    const name  = row.querySelector('.ingredient-name-input').value.trim();
    const grams = parseFloat(row.querySelector('.ingredient-grams-input').value) || 0;
    if (name) result.push({ name, grams });
  });
  return result;
}

function populateIngredientRows(ingredients) {
  document.getElementById('ingredientsList').innerHTML = '';
  rowCounter = 0;
  if (!ingredients || !ingredients.length) { addIngredientRow(); return; }
  ingredients.forEach(i => addIngredientRow(i.name, i.grams));
}

/* =========================================
   AUTOCOMPLETE
   ========================================= */
function showSuggestions(input, rowId) {
  const q      = input.value.trim().toLowerCase();
  const sugBox = document.getElementById(`sug_${rowId}`);
  if (!q) { hideSuggestions(rowId); return; }
  const matches = INGREDIENTS_DB.filter(i => i.toLowerCase().includes(q)).slice(0, 8);
  if (!matches.length) { hideSuggestions(rowId); return; }
  sugBox.innerHTML = matches.map(m => {
    const safe = escHtml(m);
    const hi   = safe.replace(new RegExp(`(${escRegex(q)})`, 'gi'), '<mark>$1</mark>');
    return `<div class="suggestion-item" onmousedown="selectSuggestion('${rowId}','${safe}')">${hi}</div>`;
  }).join('');
  sugBox.classList.add('open');
  activeSugIdx = -1;
}
function hideSuggestions(rowId) {
  const el = document.getElementById(`sug_${rowId}`);
  if (el) { el.classList.remove('open'); el.innerHTML = ''; }
  activeSugIdx = -1;
}
function hideSuggestionsDelayed(rowId) { setTimeout(() => hideSuggestions(rowId), 150); }
function selectSuggestion(rowId, value) {
  const row = document.getElementById(rowId);
  if (!row) return;
  row.querySelector('.ingredient-name-input').value = value;
  hideSuggestions(rowId);
  row.querySelector('.ingredient-grams-input').focus();
}
function navigateSuggestions(e, rowId) {
  const sugBox = document.getElementById(`sug_${rowId}`);
  if (!sugBox?.classList.contains('open')) return;
  const items = sugBox.querySelectorAll('.suggestion-item');
  if (!items.length) return;
  if (e.key === 'ArrowDown')  { e.preventDefault(); activeSugIdx = Math.min(activeSugIdx + 1, items.length - 1); }
  else if (e.key === 'ArrowUp')   { e.preventDefault(); activeSugIdx = Math.max(activeSugIdx - 1, -1); }
  else if (e.key === 'Enter' && activeSugIdx >= 0) { e.preventDefault(); items[activeSugIdx].dispatchEvent(new MouseEvent('mousedown')); return; }
  else if (e.key === 'Escape') { hideSuggestions(rowId); return; }
  items.forEach((el, i) => el.classList.toggle('active', i === activeSugIdx));
  if (activeSugIdx >= 0) items[activeSugIdx].scrollIntoView({ block: 'nearest' });
}

/* =========================================
   IMAGE UPLOAD
   ========================================= */
function handleImageUpload(input) {
  const file = input.files[0];
  if (!file) return;
  document.getElementById('uploadFilename').textContent = '📎 ' + file.name;
  document.getElementById('uploadFilename').style.display = 'block';
  const reader = new FileReader();
  reader.onload = e => {
    document.getElementById('imagePreview').src = e.target.result;
    document.getElementById('imagePreviewWrap').style.display = 'block';
  };
  reader.readAsDataURL(file);
}

function setupDragDrop() {
  const zone = document.getElementById('uploadZone');
  if (!zone) return;
  zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('dragover'); });
  zone.addEventListener('dragleave', () => zone.classList.remove('dragover'));
  zone.addEventListener('drop', e => {
    e.preventDefault(); zone.classList.remove('dragover');
    const file = e.dataTransfer.files[0];
    if (file?.type.startsWith('image/')) {
      const input = document.getElementById('recipeImage');
      const dt = new DataTransfer(); dt.items.add(file); input.files = dt.files;
      handleImageUpload(input);
    }
  });
}

/* =========================================
   FORM — CRUD
   ========================================= */
function setupForm() {
  document.getElementById('recipeForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const name         = document.getElementById('recipeName').value.trim();
    const instructions = document.getElementById('instructions').value.trim();
    const ingredients  = getIngredientRows();
    const imageEl      = document.getElementById('imagePreview');
    const image        = (imageEl.src && imageEl.src !== window.location.href) ? imageEl.src : null;

    if (!name)              { showToast('Please enter a recipe name.', 'error');        return; }
    if (!ingredients.length){ showToast('Add at least one ingredient.', 'error');       return; }
    if (!instructions)      { showToast('Please add instructions.', 'error');           return; }

    const editIndex = document.getElementById('editIndex').value;

    if (editIndex !== '') {
      const idx = parseInt(editIndex, 10);
      recipes[idx] = { ...recipes[idx], name, ingredients, instructions, image };
      showToast('Recipe updated!', 'success');
    } else {
      recipes.unshift({ id: uid(), name, ingredients, instructions, image, created: new Date().toISOString() });
      showToast('Recipe saved!', 'success');
    }

    saveRecipes();
    renderMyRecipes();
    updateHeroCount();
    resetForm();
    document.getElementById('recipes-list').scrollIntoView({ behavior: 'smooth', block: 'start' });
  });
}

function resetForm() {
  document.getElementById('recipeForm').reset();
  document.getElementById('editIndex').value = '';
  document.getElementById('imagePreviewWrap').style.display = 'none';
  document.getElementById('uploadFilename').style.display   = 'none';
  document.getElementById('imagePreview').src = '';
  document.getElementById('formHeading').textContent    = '✦ Create Recipe';
  document.getElementById('formSubheading').textContent = 'Fill in the details below to save your recipe to the collection.';
  document.getElementById('submitBtnText').textContent   = 'Save Recipe';
  const editBtn = document.getElementById('modalEditBtn');
  if (editBtn) { editBtn.style.display = ''; editBtn.textContent = 'Edit'; }
  populateIngredientRows([]);
}

function editRecipe(idx) {
  const r = recipes[idx];
  if (!r) return;
  document.getElementById('editIndex').value    = idx;
  document.getElementById('recipeName').value   = r.name;
  document.getElementById('instructions').value = r.instructions;
  document.getElementById('formHeading').textContent    = '✏️ Edit Recipe';
  document.getElementById('formSubheading').textContent = 'Update the details below and save your changes.';
  document.getElementById('submitBtnText').textContent   = 'Update Recipe';
  populateIngredientRows(r.ingredients);
  if (r.image) {
    document.getElementById('imagePreview').src              = r.image;
    document.getElementById('imagePreviewWrap').style.display = 'block';
    document.getElementById('uploadFilename').textContent    = '📎 Existing image';
    document.getElementById('uploadFilename').style.display   = 'block';
  }
  document.getElementById('add-recipe').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function deleteRecipe(idx) {
  if (!confirm(`Delete "${recipes[idx]?.name}"?`)) return;
  recipes.splice(idx, 1);
  saveRecipes();
  renderMyRecipes();
  updateHeroCount();
  showToast('Recipe deleted.', 'info');
}

/* =========================================
   RENDER MY RECIPES — empty by default
   ========================================= */
function renderMyRecipes() {
  const grid  = document.getElementById('recipesGrid');
  const count = document.getElementById('recipeCount');
  count.textContent = recipes.length + (recipes.length === 1 ? ' recipe' : ' recipes');

  if (!recipes.length) {
    grid.innerHTML = `
      <div class="empty-state">
        <div class="empty-state-visual">🍽️</div>
        <h3>Your Recipe Book is Empty</h3>
        <p>
          You haven't saved any recipes yet. Use the form above to create your first one —
          add ingredients, steps, and a photo!
        </p>
        <button class="btn btn-gold" onclick="scrollToForm()">
          + Create My First Recipe
        </button>
      </div>`;
    return;
  }

  grid.innerHTML = recipes.map((r, idx) => myRecipeCardHTML(r, idx)).join('');
}

function myRecipeCardHTML(r, idx) {
  const imageHTML = r.image
    ? `<img class="recipe-card-image" src="${escHtml(r.image)}" alt="${escHtml(r.name)}" loading="lazy" />`
    : `<div class="recipe-card-image-placeholder">🍴<span class="recipe-card-badge">${r.ingredients.length} ingr.</span></div>`;

  const tags = r.ingredients.slice(0, 4).map(i =>
    `<span class="ingredient-tag">${escHtml(i.name)}</span>`
  ).join('');
  const more = r.ingredients.length > 4
    ? `<span class="ingredient-tag more">+${r.ingredients.length - 4}</span>` : '';

  const preview = (r.instructions || '').slice(0, 120) + (r.instructions.length > 120 ? '…' : '');

  return `
    <article class="recipe-card">
      <div style="position:relative">${imageHTML}</div>
      <div class="recipe-card-body">
        <h3 class="recipe-card-name">${escHtml(r.name)}</h3>
        <div class="recipe-card-ingredients">${tags}${more}</div>
        <p class="recipe-card-instructions">${escHtml(preview)}</p>
        <div class="recipe-card-footer">
          <span class="recipe-card-meta">📅 ${formatDate(r.created)}</span>
          <div class="recipe-card-actions">
            <button class="btn btn-outline btn-sm" onclick="viewMyRecipe(${idx})">View</button>
            <button class="btn btn-secondary btn-sm" onclick="editRecipe(${idx})">Edit</button>
            <button class="btn btn-danger btn-sm" onclick="deleteRecipe(${idx})">Delete</button>
          </div>
        </div>
      </div>
    </article>`;
}

/* =========================================
   VIEW MODAL — My recipes
   ========================================= */
function viewMyRecipe(idx) {
  const r = recipes[idx];
  if (!r) return;

  document.getElementById('modalTitle').textContent = r.name;
  document.getElementById('modalImage').innerHTML = r.image
    ? `<img class="modal-image" src="${escHtml(r.image)}" alt="${escHtml(r.name)}" />`
    : `<div class="modal-image-placeholder">🍴</div>`;

  document.getElementById('modalIngredients').innerHTML = r.ingredients.map(i =>
    `<span class="modal-ingredient-tag">${escHtml(i.name)} — ${i.grams}g</span>`
  ).join('');
  document.getElementById('modalInstructions').textContent = r.instructions;

  const editBtn = document.getElementById('modalEditBtn');
  editBtn.style.display = '';
  editBtn.textContent   = 'Edit';
  editBtn.onclick = () => {
    document.getElementById('viewModal').classList.remove('open');
    editRecipe(idx);
  };

  document.getElementById('viewModal').classList.add('open');
}

function closeModal(e) {
  if (e.target === document.getElementById('viewModal'))
    document.getElementById('viewModal').classList.remove('open');
}

/* =========================================
   HERO COUNT
   ========================================= */
function updateHeroCount() {
  const el = document.getElementById('heroRecipeCount');
  if (el) el.textContent = recipes.length;
}

/* =========================================
   NAVIGATION
   ========================================= */
function scrollToForm() {
  document.getElementById('add-recipe').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function toggleMobileMenu(btn) {
  const spans = btn.querySelectorAll('span');
  btn.classList.toggle('open');
  if (btn.classList.contains('open')) {
    spans[0].style.transform = 'rotate(45deg) translate(5px,5px)';
    spans[1].style.opacity   = '0';
    spans[2].style.transform = 'rotate(-45deg) translate(5px,-5px)';
  } else {
    spans[0].style.transform = spans[1].style.opacity = spans[2].style.transform = '';
  }
}

/* =========================================
   TOAST
   ========================================= */
function showToast(message, type = 'info') {
  const icons  = { success: '✔', error: '✖', info: '✦' };
  const toast  = document.createElement('div');
  toast.className = `toast ${type}`;
  toast.innerHTML = `<span>${icons[type]||'✦'}</span>${escHtml(message)}`;
  document.getElementById('toastContainer').appendChild(toast);
  setTimeout(() => {
    toast.style.animation = 'toastOut .3s forwards';
    setTimeout(() => toast.remove(), 300);
  }, 3200);
}

/* =========================================
   UTILITIES
   ========================================= */
function uid() { return Math.random().toString(36).slice(2,10) + Date.now().toString(36); }

function escHtml(str) {
  return String(str)
    .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
    .replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

function escRegex(str) { return str.replace(/[.*+?^${}()|[\]\\]/g,'\\$&'); }

function formatDate(iso) {
  try { return new Date(iso).toLocaleDateString('en-GB',{day:'numeric',month:'short',year:'numeric'}); }
  catch { return ''; }
}