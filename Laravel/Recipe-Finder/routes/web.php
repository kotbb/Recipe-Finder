<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RecipeController;

Route::get('/', [RecipeController::class, 'index'])->name('home');

Route::post('/recipes', [RecipeController::class, 'store'])->name('recipes.store');

Route::get('/recipes/{id}', [RecipeController::class, 'show'])->name('recipes.show');

Route::get('/recipes/{id}/edit', [RecipeController::class, 'edit'])->name('recipes.edit');

Route::put('/recipes/{id}', [RecipeController::class, 'update'])->name('recipes.update');

Route::delete('/recipes/{id}', [RecipeController::class, 'destroy'])->name('recipes.destroy');

Route::prefix('api')->name('api.')->group(function () {
    Route::get('/featured-recipes', [RecipeController::class, 'featured'])->name('featured.index');
    Route::get('/featured-recipes/search', [RecipeController::class, 'searchFeatured'])->name('featured.search');
    Route::get('/recipes', [RecipeController::class, 'apiIndex'])->name('recipes.index');
    Route::post('/recipes', [RecipeController::class, 'apiStore'])->name('recipes.store');
    Route::put('/recipes/{recipe}', [RecipeController::class, 'apiUpdate'])->name('recipes.update');
    Route::delete('/recipes/{recipe}', [RecipeController::class, 'apiDestroy'])->name('recipes.destroy');
    Route::post('/recipes/upload-image', [RecipeController::class, 'uploadImage'])->name('recipes.upload-image');
});
