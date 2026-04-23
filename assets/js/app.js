'use strict';

// Page IDs
const PAGE_IDS = ['featured', 'add-recipe', 'recipes-list'];
const DEFAULT_PAGE = 'featured';

document.addEventListener('DOMContentLoaded', () => {
  renderMyRecipesShape();
  renderIngredientsShape();
  initPageRouter();
});

// Ingredient Row HTML
const INGREDIENT_ROW_HTML = `
  <div class="ingredient-row">
    <div class="ingredient-input-wrap">
      <input type="text" class="ingredient-name-input"
        placeholder="Enter ingredient..." autocomplete="off" />
    </div>
    <div class="gram-input">
      <input type="number" class="ingredient-grams-input"
        placeholder="0" min="0" max="99999" />
      <span class="gram-unit">g</span>
    </div>
    <button type="button" class="btn-icon btn-add-row" title="Add row">＋</button>
    <button type="button" class="btn-icon btn-delete-row" title="Remove">✕</button>
  </div>
`;

// Render Ingredients Shape
function renderIngredientsShape() {
  const list = document.getElementById('ingredientsList');
  if (!list) return;
  list.innerHTML = INGREDIENT_ROW_HTML;
  bindIngredientRowButtons(list);
}

// Bind Ingredient Row Buttons
function bindIngredientRowButtons(list) {
  list.addEventListener('click', event => {
    const addBtn = event.target.closest('.btn-add-row');
    const delBtn = event.target.closest('.btn-delete-row');

    if (addBtn) {
      addIngredientRow();
      return;
    }

    if (delBtn) {
      const row = delBtn.closest('.ingredient-row');
      if (!row) return;
      if (list.children.length <= 1) return;
      row.remove();
    }
  });
}

// Add Ingredient Row from the button in the form
function addIngredientRow() {
  const list = document.getElementById('ingredientsList');
  if (!list) return;
  const template = document.createElement('div');
  template.innerHTML = INGREDIENT_ROW_HTML.trim();
  const newRow = template.firstChild;
  list.appendChild(newRow);
  const nameInput = newRow.querySelector('.ingredient-name-input');
  if (nameInput) nameInput.focus();
}

// My Recipes
const MY_RECIPES_HTML = `
  <div class="empty-state">
    <div class="empty-state-visual">🍽️</div>
    <h3>Your Recipe Book is Empty</h3>
    <p>
      You haven't saved any recipes yet. Click the button below to create your first one.
    </p>
    <button type="button" class="btn btn-gold" onclick="showPage('add-recipe')">
      Create My First Recipe
    </button>
  </div>
`;

// Render My Recipes Shape
function renderMyRecipesShape() {
  const grid = document.getElementById('recipesGrid');
  if (!grid) return;
  grid.innerHTML = MY_RECIPES_HTML;
}

// Initialize Page Router
function initPageRouter() {
  document.querySelectorAll('a[href^="#"]').forEach(link => {
    link.addEventListener('click', event => {
      const href = link.getAttribute('href');
      if (!href || href === '#') return;

      const id = href.slice(1);
      if (!PAGE_IDS.includes(id)) return;

      event.preventDefault();
      showPage(id);
    });
  });

  window.addEventListener('hashchange', () => {
    const id = (location.hash || '#' + DEFAULT_PAGE).slice(1);
    showPage(id, { skipHashUpdate: true });
  });

  const initial = (location.hash || '#' + DEFAULT_PAGE).slice(1);
  showPage(PAGE_IDS.includes(initial) ? initial : DEFAULT_PAGE, { skipHashUpdate: true });
}

// Show Page
function showPage(id, options = {}) {
  if (!PAGE_IDS.includes(id)) id = DEFAULT_PAGE;

  PAGE_IDS.forEach(pid => {
    const section = document.getElementById(pid);
    if (section) section.style.display = (pid === id) ? '' : 'none';
  });

  const hero = document.querySelector('.page-hero');
  if (hero) hero.style.display = (id === 'featured') ? '' : 'none';

  document.querySelectorAll('.divider').forEach(divider => {
    divider.style.display = 'none';
  });

  document.querySelectorAll('.nav-link').forEach(link => {
    const href = link.getAttribute('href') || '';
    link.classList.toggle('active', href === '#' + id);
  });

  if (!options.skipHashUpdate) {
    history.pushState(null, '', '#' + id);
  }

  window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Scroll to Form
function scrollToForm() {
  showPage('add-recipe');
}

// Export Functions
window.showPage = showPage;
window.scrollToForm = scrollToForm;
window.addIngredientRow = addIngredientRow;
