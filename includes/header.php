<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Recipe Finder</title>
  <link rel="stylesheet" href="assets/css/style.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
</head>
<body>

<!-- ── Sticky Header ── -->
<header class="site-header">
  <div class="header-inner">

    <div class="header-brand">
      <span class="header-title">Recipe <span>Finder</span></span>
    </div>

    <nav class="header-nav" aria-label="Main navigation">
      <a href="#featured" class="nav-link">Discover</a>
      <a href="#add-recipe" class="nav-link">Add Recipe</a>
      <a href="#recipes-list" class="nav-link">My Recipes</a>
    </nav>
  </div>
</header>

<section class="page-hero" aria-label="Welcome banner">
  <div class="hero-content">
    <div class="hero-label">Your Culinary Collection</div>
    <h1>
      Cook. Create.<br>
      <em>Inspire.</em>
    </h1>
    <p>
      Build your personal recipe book - capture ingredients, steps,
      and photos in one beautifully crafted space.
    </p>
    <div class="hero-actions">
      <button class="btn btn-gold" onclick="scrollToForm()">+ Add Your Recipe</button>
      <button class="btn btn-ghost" onclick="document.getElementById('featured').scrollIntoView({behavior:'smooth'})">
        Discover Recipes ↓
      </button>
    </div>
  </div>

</section>