<?php include 'includes/header.php'; ?>

<main id="app">

    <section id="addRecipe" class="section active">
        <h2>Add Recipe</h2>
        <form onsubmit="event.preventDefault(); addRecipe();">
            <input type="text" id="name" placeholder="Recipe Name" required>
            <textarea id="ingredients" placeholder="Ingredients" required></textarea>
            <textarea id="instructions" placeholder="Instructions" required></textarea>
            <input type="file" id="image">
            <button type="submit">Add Recipe</button>
        </form>
    </section>

    <section id="myRecipes" class="section hidden">
        <h2>My Recipes</h2>
        <div id="recipesContainer"></div>
    </section>

    <section id="searchAPI" class="section hidden">
        <h2>Search Recipes</h2>
        <form onsubmit="event.preventDefault(); searchRecipes();">
            <input type="text" id="search" placeholder="Search recipe...">
            <button type="submit">Search</button>
        </form>
        <div id="results"></div>
    </section>

</main>

<script src="assets/js/app.js"></script>
<script src="assets/js/recipes.js"></script>
<script src="assets/js/API_Ops.js"></script>

<?php include 'includes/footer.php'; ?>