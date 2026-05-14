'use strict';

document.addEventListener('DOMContentLoaded', () => {
  loadFeaturedRecipes();
});

async function loadFeaturedRecipes() {
  const grid = document.getElementById('featuredGrid');
  if (!grid) return;

  grid.innerHTML = buildSkeletons(8);

  try {
    const recipes = await fetchFeaturedRecipes();
    renderFeaturedCards(grid, recipes);
  } catch (err) {
    grid.innerHTML = `
      <div style="text-align:center;padding:40px;color:var(--ink-soft)">
        <div style="font-size:2rem;margin-bottom:10px"</div>
        <p>Could not load featured recipes.</p>
        <button class="btn btn-outline btn-sm" style="margin-top:12px"
          onclick="loadFeaturedRecipes()">Retry</button>
      </div>`;
  }
}

function buildSkeletons(n) {
  const card = `
    <div class="skeleton-card">
      <div class="skeleton-img"></div>
      <div class="skeleton-body">
        <div class="skeleton-line med"></div>
        <div class="skeleton-line short"></div>
      </div>
    </div>`;
  return `<div class="featured-skeleton">${card.repeat(n)}</div>`;
}

function renderFeaturedCards(grid, recipes) {
  if (!recipes || !recipes.length) {
    grid.innerHTML = '<p style="padding:20px;color:var(--ink-soft)">No featured recipes found.</p>';
    return;
  }
  grid.innerHTML = recipes.map(r => featuredCardHTML(r)).join('');
}

function featuredCardHTML(r) {
  const img = r.image
    ? `<img class="featured-card-img" src="${escHtml(r.image)}" alt="${escHtml(r.name)}" loading="lazy"
         onerror="this.parentElement.innerHTML='<div class=featured-card-img style=background:var(--cream-dark);display:flex;align-items:center;justify-content:center;font-size:3rem>🍽️</div>'" />`
    : `<div class="featured-card-img" style="background:var(--cream-dark);display:flex;align-items:center;justify-content:center;font-size:3rem">🍽️</div>`;

  const tags = Array.isArray(r.tags)
    ? r.tags.slice(0, 2).map(t => `<span class="featured-card-tag">${escHtml(t)}</span>`).join('')
    : '';

  const safeJson = escAttr(JSON.stringify(r));

  return `
    <article class="featured-card" role="button" tabindex="0"
             aria-label="View ${escHtml(r.name)}"
             onclick="openFeaturedModal('${safeJson}')"
             onkeydown="if(event.key==='Enter')openFeaturedModal('${safeJson}')">
      <div class="featured-card-img-wrap">${img}</div>
      <div class="featured-card-body">
        <h3 class="featured-card-name">${escHtml(r.name)}</h3>
        <div class="featured-card-meta">${tags}</div>
        <span class="featured-card-link">View recipe →</span>
      </div>
    </article>`;
}

function openFeaturedModal(safeJson) {
  let r;
  try { r = JSON.parse(safeJson); } catch { return; }

  const ingredients = Array.isArray(r.tags)
    ? r.tags.map(t => `<span class="modal-ingredient-tag">${escHtml(t)}</span>`).join('')
    : '';

  const instructions = Array.isArray(r.instructions)
    ? r.instructions.join('\n')
    : (r.instructions || 'Visit the recipe source for full instructions.');

  fillAndOpenModal({
    imageSrc: r.image || null,
    imageAlt: r.name,
    title: r.name,
    ingredients,
    instructions,
    editLabel: null,
  });
}

window.loadFeaturedRecipes = loadFeaturedRecipes;
window.openFeaturedModal = openFeaturedModal;
