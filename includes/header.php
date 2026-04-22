<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Recipe Manager</title>
  <link rel="stylesheet" href="../assets/css/style.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
</head>
<body>

<!-- ── Sticky Header ── -->
<header class="site-header">
  <div class="header-inner">

    <div class="header-brand">
      <div class="header-logo">🍳</div>
      <span class="header-title">Recipe <span>Manager</span></span>
    </div>

    <nav class="header-nav" aria-label="Main navigation">
      <a href="#featured" class="nav-link">Discover</a>
      <a href="#add-recipe" class="nav-link">Add Recipe</a>
      <a href="#recipes-list" class="nav-link">My Recipes</a>
    </nav>

    <div style="display:flex;align-items:center;gap:12px">
      <button class="btn-header" onclick="scrollToForm()">+ New Recipe</button>
      <button class="hamburger" aria-label="Toggle menu" onclick="toggleMobileMenu(this)">
        <span></span><span></span><span></span>
      </button>
    </div>

  </div>
</header>

<!-- ── Hero — Full-bleed Background Photo ── -->
<!--
  📸 PHOTO CREDIT & RECOMMENDATION:
  "Food flatlay on dark wood" by Brooke Lark
  Source: Unsplash — https://unsplash.com/photos/nMffL1zjbw4
  License: Free for commercial use (Unsplash License)

  WHY THIS PHOTO:
  - Warm, rustic overhead flat-lay with colourful fresh produce
  - Dark wooden surface perfectly complements the olive/ink/gold palette
  - High resolution (6000×4000px) — never pixelates even on 4K screens
  - Generous negative space on the left for text overlay

  TO USE YOUR OWN LOCAL PHOTO:
  1. Download it: https://unsplash.com/photos/nMffL1zjbw4/download
  2. Save as: img/hero.jpg (create an /img/ folder next to this file)
  3. In style.css, change the background-image URL to: url('img/hero.jpg')
  This removes the CDN dependency and loads faster in production.
-->
<section class="page-hero" aria-label="Welcome banner">
  <div class="hero-content">
    <div class="hero-label">Your Culinary Collection</div>
    <h1>
      Cook. Create.<br>
      <em>Inspire.</em>
    </h1>
    <p>
      Build your personal recipe book — capture ingredients, steps,
      and photos in one beautifully crafted space.
    </p>
    <div class="hero-actions">
      <button class="btn btn-gold" onclick="scrollToForm()">+ Add Your Recipe</button>
      <button class="btn btn-ghost" onclick="document.getElementById('featured').scrollIntoView({behavior:'smooth'})">
        Discover Recipes ↓
      </button>
    </div>
  </div>

  <!-- Stat bar -->
  <div class="hero-stat-bar">
    <div class="hero-stat-inner">
      <div class="hero-stat">
        <span class="hero-stat-icon">🍽️</span>
        <div class="hero-stat-text">
          <span class="hero-stat-num" id="heroRecipeCount">0</span>
          <span class="hero-stat-label">My Recipes</span>
        </div>
      </div>
      <div class="hero-stat">
        <span class="hero-stat-icon">🌍</span>
        <div class="hero-stat-text">
          <span class="hero-stat-num">50,000+</span>
          <span class="hero-stat-label">Global Recipes</span>
        </div>
      </div>
      <div class="hero-stat">
        <span class="hero-stat-icon">🥗</span>
        <div class="hero-stat-text">
          <span class="hero-stat-num">14</span>
          <span class="hero-stat-label">Cuisine Types</span>
        </div>
      </div>
    </div>
  </div>
</section>