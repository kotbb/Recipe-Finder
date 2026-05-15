<?php

use App\Support\RecipePayloadValidator;

it('accepts a valid ingredients JSON payload', function () {
    $payload = json_encode([
        ['name' => 'Tomato', 'grams' => '120'],
        ['name' => 'Salt', 'grams' => '5'],
    ]);

    expect(RecipePayloadValidator::validateIngredientsJson($payload))->toBeNull();
});

it('rejects empty ingredient names', function () {
    $payload = json_encode([
        ['name' => '', 'grams' => '10'],
    ]);

    expect(RecipePayloadValidator::validateIngredientsJson($payload))
        ->toBe('Each ingredient must have a non-empty name.');
});
