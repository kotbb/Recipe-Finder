<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
        $data = $request->validate([
            'name'         => 'required|string|max:255|unique:recipes,name',
            'ingredients'  => 'required|string',
            'instructions' => 'required|string',
            'image_path'   => 'nullable|image|max:5120',
        ]);

        if ($request->hasFile('image_path')) {
            $data['image_path'] = $request->file('image_path')->store('uploads', 'public');
        } else {
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

        $data = $request->validate([
            'name'         => 'required|string|max:255|unique:recipes,name,' . $recipe->id,
            'ingredients'  => 'required|string',
            'instructions' => 'required|string',
            'image_path'   => 'nullable|image|max:5120',
        ]);

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
}
