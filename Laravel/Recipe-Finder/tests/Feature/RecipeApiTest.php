<?php

use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('creates a recipe through the Laravel JSON endpoint', function () {
    $payload = [
        'name' => 'Lemon Pasta',
        'ingredients' => json_encode([
            ['name' => 'Pasta', 'grams' => '200'],
            ['name' => 'Lemon', 'grams' => '50'],
        ]),
        'instructions' => 'Boil pasta, add lemon, and toss.',
        'image_path' => '/storage/uploads/lemon-pasta.jpg',
    ];

    $this->postJson(route('api.recipes.store'), $payload)
        ->assertCreated()
        ->assertJsonPath('success', true);

    $this->assertDatabaseHas('recipes', [
        'name' => 'Lemon Pasta',
        'instructions' => 'Boil pasta, add lemon, and toss.',
    ]);
});

it('returns validation errors for invalid recipe data', function () {
    $this->postJson(route('api.recipes.store'), [
        'name' => '',
        'ingredients' => json_encode([['name' => 'Pasta', 'grams' => '10']]),
        'instructions' => '',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'instructions']);
});

it('returns validation errors for invalid ingredient rows', function () {
    $this->postJson(route('api.recipes.store'), [
        'name' => 'Invalid Ingredients',
        'ingredients' => json_encode([['name' => '', 'grams' => '10']]),
        'instructions' => 'Cook it.',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['ingredients']);
});

it('uploads a recipe image and returns a public storage path', function () {
    Storage::fake('public');
    $path = tempnam(sys_get_temp_dir(), 'recipe-image');
    file_put_contents($path, base64_decode('R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw=='));

    $this->postJson(route('api.recipes.upload-image'), [
        'recipe_image' => new UploadedFile($path, 'soup.gif', 'image/gif', null, true),
    ])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonStructure(['file_path', 'storage_path']);
});

it('handles third party API failures with a friendly JSON error', function () {
    Http::fake([
        'dummyjson.com/*' => Http::response(['error' => 'down'], 500),
    ]);

    $this->getJson(route('api.featured.index'))
        ->assertServiceUnavailable()
        ->assertJsonPath('success', false)
        ->assertJsonPath('error', 'Featured recipes are unavailable right now. Please try again later.');
});
