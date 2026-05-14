<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RecipeController;

Route::get('/', [RecipeController::class, 'index'])->name('home');

Route::post('/recipes', [RecipeController::class, 'store'])->name('recipes.store');

Route::get('/recipes/{id}', [RecipeController::class, 'show'])->name('recipes.show');

Route::get('/recipes/{id}/edit', [RecipeController::class, 'edit'])->name('recipes.edit');

Route::put('/recipes/{id}', [RecipeController::class, 'update'])->name('recipes.update');

Route::delete('/recipes/{id}', [RecipeController::class, 'destroy'])->name('recipes.destroy');
