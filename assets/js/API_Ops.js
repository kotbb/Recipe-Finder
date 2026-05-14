'use strict';

const API_OPS_BASE = 'backend/API_Ops.php';
const DB_OPS_BASE = 'backend/DB_Ops.php';
const UPLOAD_BASE = 'backend/Upload.php';

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
  const res = await fetch(`${API_OPS_BASE}?action=getAll`);
  const json = await parseJsonResponse(res);

  if (!json.success) {
    throw new Error(json.error || 'Failed to load featured recipes');
  }

  return json.data || [];
}

async function fetchMyRecipes() {
  const res = await fetch(`${DB_OPS_BASE}?action=getAll`);
  const json = await parseJsonResponse(res);

  if (!json.success) {
    throw new Error(json.error || 'Failed to load your recipes');
  }

  return json.data || [];
}

async function addMyRecipe(recipe) {
  const res = await fetch(`${DB_OPS_BASE}?action=add`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(recipe)
  });

  const json = await parseJsonResponse(res);

  if (!json.success) {
    throw new Error(json.error || 'Add failed');
  }

  return json.id;
}

async function updateMyRecipe(id, recipe) {
  const res = await fetch(`${DB_OPS_BASE}?action=update&id=${encodeURIComponent(id)}`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(recipe)
  });

  const json = await parseJsonResponse(res);

  if (!json.success) {
    throw new Error(json.error || 'Update failed');
  }
}

async function deleteMyRecipe(id) {
  const res = await fetch(`${DB_OPS_BASE}?action=delete&id=${encodeURIComponent(id)}`, {
    method: 'DELETE'
  });

  const json = await parseJsonResponse(res);

  if (!json.success) {
    throw new Error(json.error || 'Delete failed');
  }
}

async function uploadRecipeImage(file) {
  if (!file) return '';

  const formData = new FormData();
  formData.append('recipe_image', file);

  const res = await fetch(UPLOAD_BASE, {
    method: 'POST',
    body: formData
  });

  const json = await parseJsonResponse(res);

  if (!json.success) {
    throw new Error(json.error || 'Image upload failed');
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