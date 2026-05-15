<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Services\FeaturedRecipeService;
use App\Support\RecipePayloadValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class RecipeController extends Controller
{
    // GET /
    public function index()
    {
        $recipes = Recipe::orderBy('id', 'desc')->get();
        return view('home', compact('recipes'));
    }

    // POST /recipes
    public function store(Request $request)
    {
        $data = $this->validatedRecipeData($request);

        if ($request->hasFile('image_path')) {
            $data['image_path'] = $request->file('image_path')->store('uploads', 'public');
        } elseif (!$request->filled('image_path')) {
            $data['image_path'] = null;
        }

        Recipe::create($data);

        return redirect()->route('home')->with('success', 'Recipe added successfully!');
    }

    // GET /recipes/{id}
    public function show($id)
    {
        $recipe = Recipe::findOrFail($id);
        return view('recipes.show', compact('recipe'));
    }

    // GET /recipes/{id}/edit
    public function edit($id)
    {
        $recipe = Recipe::findOrFail($id);
        return view('recipes.edit', compact('recipe'));
    }

    // PUT /recipes/{id}
    public function update(Request $request, $id)
    {
        $recipe = Recipe::findOrFail($id);

        $data = $this->validatedRecipeData($request);

        if ($request->hasFile('image_path')) {
            if ($recipe->image_path) {
                Storage::disk('public')->delete($recipe->image_path);
            }
            $data['image_path'] = $request->file('image_path')->store('uploads', 'public');
        } else {
            unset($data['image_path']);
        }

        $recipe->update($data);

        return redirect()->route('home')->with('success', 'Recipe updated successfully!');
    }

    // DELETE /recipes/{id}
    public function destroy($id)
    {
        $recipe = Recipe::findOrFail($id);

        if ($recipe->image_path) {
            Storage::disk('public')->delete($recipe->image_path);
        }

        $recipe->delete();

        return redirect()->route('home')->with('success', 'Recipe deleted successfully!');
    }

    public function apiIndex()
    {
        return response()->json([
            'success' => true,
            'data' => Recipe::orderByDesc('id')->get()->map(fn (Recipe $recipe) => $this->recipePayload($recipe)),
        ]);
    }

    public function apiStore(Request $request)
    {
        $recipe = Recipe::create($this->validatedRecipeData($request));

        return response()->json([
            'success' => true,
            'id' => $recipe->id,
            'data' => $this->recipePayload($recipe),
        ], 201);
    }

    public function apiUpdate(Request $request, Recipe $recipe)
    {
        $recipe->update($this->validatedRecipeData($request));

        return response()->json([
            'success' => true,
            'data' => $this->recipePayload($recipe->fresh()),
        ]);
    }

    public function apiDestroy(Recipe $recipe)
    {
        if ($recipe->image_path && !str_starts_with($recipe->image_path, '/storage/')) {
            Storage::disk('public')->delete($recipe->image_path);
        }

        $recipe->delete();

        return response()->json(['success' => true]);
    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'recipe_image' => ['required', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'],
        ], [
            'recipe_image.required' => 'No file was received.',
            'recipe_image.image' => 'File is not an image.',
            'recipe_image.mimes' => 'Only JPG, JPEG, PNG, GIF and WEBP files are allowed.',
            'recipe_image.max' => 'Image must be at most 5 MB.',
        ]);

        $path = $request->file('recipe_image')->store('uploads', 'public');

        return response()->json([
            'success' => true,
            'file_path' => Storage::url($path),
            'storage_path' => $path,
        ]);
    }

    public function featured(FeaturedRecipeService $recipes)
    {
        $result = $recipes->all();

        return response()->json([
            'success' => $result['ok'],
            'error' => $result['message'],
            'data' => $result['recipes'],
        ], $result['ok'] ? 200 : 503);
    }

    public function searchFeatured(Request $request, FeaturedRecipeService $recipes)
    {
        $data = $request->validate([
            'query' => ['required', 'string', 'max:100'],
        ]);

        $result = $recipes->search($data['query']);

        return response()->json([
            'success' => $result['ok'],
            'error' => $result['message'],
            'data' => $result['recipes'],
        ], $result['ok'] ? 200 : 503);
    }

    private function validatedRecipeData(Request $request): array
    {
        $imageRules = $request->hasFile('image_path')
            ? ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120']
            : ['nullable', 'string', 'max:' . RecipePayloadValidator::IMAGE_PATH_MAX_LENGTH];

        $data = $request->validate([
            'name' => ['required', 'string', 'max:' . RecipePayloadValidator::NAME_MAX_LENGTH],
            'ingredients' => ['required', 'string', 'max:' . RecipePayloadValidator::TEXT_MAX_LENGTH],
            'instructions' => ['required', 'string', 'max:' . RecipePayloadValidator::TEXT_MAX_LENGTH],
            'image_path' => $imageRules,
        ]);

        $ingredientError = RecipePayloadValidator::validateIngredientsJson($data['ingredients'] ?? null);

        if ($ingredientError !== null) {
            throw ValidationException::withMessages([
                'ingredients' => $ingredientError,
            ]);
        }

        if (isset($data['image_path']) && is_string($data['image_path'])) {
            $data['image_path'] = $this->normalizeImagePath($data['image_path']);
        }

        return $data;
    }

    private function normalizeImagePath(string $path): string
    {
        $path = trim($path);
        $storagePrefix = '/storage/';
        $storagePosition = strpos($path, $storagePrefix);

        if ($storagePosition !== false) {
            return substr($path, $storagePosition + strlen($storagePrefix));
        }

        return $path;
    }

    private function recipePayload(Recipe $recipe): array
    {
        return [
            'id' => $recipe->id,
            'name' => $recipe->name,
            'ingredients' => $recipe->ingredients,
            'instructions' => $recipe->instructions,
            'image_path' => $recipe->image_url,
            'created_at' => $recipe->created_at,
        ];
    }
}
