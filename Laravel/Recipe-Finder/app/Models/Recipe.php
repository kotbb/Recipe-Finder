<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'ingredients',
        'instructions',
        'image_path',
    ];

    protected $appends = [
        'image_url',
    ];

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image_path) {
            return null;
        }

        if (str_starts_with($this->image_path, 'http://') ||
            str_starts_with($this->image_path, 'https://') ||
            str_starts_with($this->image_path, '/storage/')) {
            return $this->image_path;
        }

        return asset('storage/' . ltrim($this->image_path, '/'));
    }
}
