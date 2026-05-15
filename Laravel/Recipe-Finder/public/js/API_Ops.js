'use strict';

const API_OPS_BASE = '/api/featured-recipes';
const DB_OPS_BASE = '/api/recipes';
const UPLOAD_BASE = '/api/recipes/upload-image';

function csrfHeaders(extra = {}) {
  const token = document.querySelector('meta[name="csrf-token"]')?.content;
  return token ? { ...extra, 'X-CSRF-TOKEN': token } : extra;
}

async function parseJsonResponse(res) {
  const text = await res.text();

  if (!text) {
    throw new Error('Empty response from server.');
  }

  try {
    return JSON.parse(text);
  } catch {
    console.error('Server response:', text);
    throw new Error('Invalid JSON from server.');
  }
}

async function fetchFeaturedRecipes() {
  const res = await fetch(API_OPS_BASE);
  const json = await parseJsonResponse(res);

  if (!json.success) {
    throw new Error(json.error || json.message || 'Failed to load featured recipes');
  }

  return json.data || [];
}

async function fetchMyRecipes() {
  const res = await fetch(DB_OPS_BASE);
  const json = await parseJsonResponse(res);

  if (!json.success) {
    throw new Error(json.error || json.message || 'Failed to load your recipes');
  }

  return json.data || [];
}

async function addMyRecipe(recipe) {
  const res = await fetch(DB_OPS_BASE, {
    method: 'POST',
    headers: csrfHeaders({ 'Content-Type': 'application/json', 'Accept': 'application/json' }),
    body: JSON.stringify(recipe)
  });

  const json = await parseJsonResponse(res);

  if (!json.success) {
    throw new Error(json.error || json.message || 'Add failed');
  }

  return json.id;
}

async function updateMyRecipe(id, recipe) {
  const res = await fetch(`${DB_OPS_BASE}/${encodeURIComponent(id)}`, {
    method: 'PUT',
    headers: csrfHeaders({ 'Content-Type': 'application/json', 'Accept': 'application/json' }),
    body: JSON.stringify(recipe)
  });

  const json = await parseJsonResponse(res);

  if (!json.success) {
    throw new Error(json.error || json.message || 'Update failed');
  }
}

async function deleteMyRecipe(id) {
  const res = await fetch(`${DB_OPS_BASE}/${encodeURIComponent(id)}`, {
    method: 'DELETE',
    headers: csrfHeaders({ 'Accept': 'application/json' })
  });

  const json = await parseJsonResponse(res);

  if (!json.success) {
    throw new Error(json.error || json.message || 'Delete failed');
  }
}

async function uploadRecipeImage(file) {
  if (!file) return '';

  const formData = new FormData();
  formData.append('recipe_image', file);

  const res = await fetch(UPLOAD_BASE, {
    method: 'POST',
    headers: csrfHeaders({ 'Accept': 'application/json' }),
    body: formData
  });

  const json = await parseJsonResponse(res);

  if (!json.success) {
    throw new Error(json.error || json.message || 'Image upload failed');
  }

  return json.file_path;
}

window.fetchFeaturedRecipes = fetchFeaturedRecipes;
window.fetchMyRecipes = fetchMyRecipes;
window.addMyRecipe = addMyRecipe;
window.updateMyRecipe = updateMyRecipe;
window.deleteMyRecipe = deleteMyRecipe;
window.uploadRecipeImage = uploadRecipeImage;
window.parseJsonResponse = parseJsonResponse;
