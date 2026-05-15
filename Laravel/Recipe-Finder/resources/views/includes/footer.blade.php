<footer class="site-footer">
  <div class="footer-inner">
    <div>
      <div class="footer-brand">
        <span class="header-title">Recipe <span>Finder</span></span>
      </div>
      <p class="footer-tagline">
        A beautifully crafted space to save, organise, and celebrate your favourite recipes from around the world.
      </p>
    </div>
    <div class="footer-links">
      <a href="#featured">Discover</a>
      <a href="#add-recipe">Add Recipe</a>
      <a href="#recipes-list">My Recipes</a>
      <a href="#">Export</a>
    </div>
  </div>
  <div class="footer-bottom">
    <div class="footer-bottom-inner">
      <span class="footer-copy">&copy; {{ date('Y') }} Recipe Finder. All rights reserved.</span>
    </div>
  </div>
</footer>

<!-- Toast Container -->
<div class="toast-container" id="toastContainer"></div>

<!-- View Modal -->
<div class="modal-overlay" id="viewModal" onclick="closeModal(event)">
  <div class="modal" id="modalContent">
    <div id="modalImage"></div>
    <div class="modal-body">
      <h2 class="modal-title" id="modalTitle">—</h2>
      <p class="modal-label">Ingredients</p>
      <div class="modal-ingredients" id="modalIngredients"></div>
      <p class="modal-label">Instructions</p>
      <p class="modal-instructions" id="modalInstructions">—</p>
      <div class="modal-footer">
        <button class="btn btn-secondary btn-sm" onclick="document.getElementById('viewModal').classList.remove('open')">Close</button>
        <button class="btn btn-outline btn-sm" id="modalEditBtn">Edit</button>
      </div>
    </div>
  </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal-overlay" id="deleteConfirmModal" onclick="closeDeleteConfirm(event)">
  <div class="confirm-modal" role="dialog" aria-modal="true" aria-labelledby="deleteConfirmTitle">
    <div class="confirm-modal-icon">!</div>
    <div class="confirm-modal-body">
      <h2 class="modal-title" id="deleteConfirmTitle">Delete recipe?</h2>
      <p class="modal-instructions">This recipe will be removed from your collection. This action cannot be undone.</p>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" onclick="closeDeleteConfirm()">Cancel</button>
        <button type="button" class="btn btn-danger btn-sm" onclick="confirmDeleteRecipe()">Delete</button>
      </div>
    </div>
  </div>
</div>
