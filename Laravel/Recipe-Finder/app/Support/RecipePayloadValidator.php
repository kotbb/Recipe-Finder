<?php

namespace App\Support;

class RecipePayloadValidator
{
    public const NAME_MAX_LENGTH = 255;
    public const TEXT_MAX_LENGTH = 65535;
    public const IMAGE_PATH_MAX_LENGTH = 500;
    public const GRAM_MAX = 99999;

    public static function validateIngredientsJson(?string $ingredients): ?string
    {
        if ($ingredients === null || trim($ingredients) === '') {
            return 'Ingredients are required.';
        }

        if (mb_strlen($ingredients) > self::TEXT_MAX_LENGTH) {
            return 'Ingredients data is too long.';
        }

        $decoded = json_decode($ingredients, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return 'Ingredients must be valid JSON.';
        }

        if (!is_array($decoded) || count($decoded) < 1) {
            return 'Add at least one ingredient.';
        }

        foreach ($decoded as $item) {
            if (!is_array($item)) {
                return 'Each ingredient must be an object with a name.';
            }

            $name = trim((string) ($item['name'] ?? ''));
            $grams = trim((string) ($item['grams'] ?? ''));

            if ($name === '') {
                return 'Each ingredient must have a non-empty name.';
            }

            if ($grams !== '' && (!is_numeric($grams) || (float) $grams < 0 || (float) $grams > self::GRAM_MAX)) {
                return 'Gram amount must be between 0 and ' . self::GRAM_MAX . '.';
            }
        }

        return null;
    }
}
