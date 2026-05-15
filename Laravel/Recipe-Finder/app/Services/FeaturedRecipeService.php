<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FeaturedRecipeService
{
    public function all(): array
    {
        return $this->request(config('services.dummyjson.recipes_url', 'https://dummyjson.com/recipes'));
    }

    public function search(string $query): array
    {
        $baseUrl = rtrim(config('services.dummyjson.recipes_url', 'https://dummyjson.com/recipes'), '/');

        return $this->request($baseUrl . '/search', ['q' => $query]);
    }

    private function request(string $url, array $query = []): array
    {
        try {
            $response = Http::timeout(10)->acceptJson()->get($url, $query);
        } catch (ConnectionException $exception) {
            Log::warning('Recipe API connection failed', ['message' => $exception->getMessage()]);

            return [
                'ok' => false,
                'message' => 'Featured recipes are unavailable right now. Please try again later.',
                'recipes' => [],
            ];
        }

        if (!$response->successful()) {
            Log::warning('Recipe API returned an error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'ok' => false,
                'message' => 'Featured recipes are unavailable right now. Please try again later.',
                'recipes' => [],
            ];
        }

        $recipes = $response->json('recipes');

        if (!is_array($recipes)) {
            Log::warning('Recipe API returned an invalid payload', ['payload' => $response->json()]);

            return [
                'ok' => false,
                'message' => 'Featured recipes returned an unexpected response.',
                'recipes' => [],
            ];
        }

        return [
            'ok' => true,
            'message' => null,
            'recipes' => $recipes,
        ];
    }
}
