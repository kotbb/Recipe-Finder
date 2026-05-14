<?php
class Recipe
{
    public const NAME_MAX_LENGTH = 255;
    public const IMAGE_PATH_MAX_LENGTH = 500;
    public const TEXT_MAX_LENGTH = 65535;

    private ?int $id = null;
    private string $name;
    private string $ingredients;
    private string $instructions;
    private ?string $imagePath = null;

    public function __construct(string $name, string $ingredients, string $instructions)
    {
        $this->name         = $name;
        $this->ingredients  = $ingredients;
        $this->instructions = $instructions;
    }

    public static function validateApiPayload(string $name, string $ingredients, string $instructions, string $imagePathRaw): ?string
    {
        if ($name === '') {
            return 'Recipe name is required.';
        }
        if (mb_strlen($name) > self::NAME_MAX_LENGTH) {
            return 'Recipe name must be at most ' . self::NAME_MAX_LENGTH . ' characters.';
        }

        if ($ingredients === '') {
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
            $ingName = isset($item['name']) ? trim((string) $item['name']) : '';
            if ($ingName === '') {
                return 'Each ingredient must have a non-empty name.';
            }
        }

        if ($instructions === '') {
            return 'Instructions are required.';
        }
        if (mb_strlen($instructions) > self::TEXT_MAX_LENGTH) {
            return 'Instructions are too long.';
        }

        $imagePath = trim($imagePathRaw);
        if ($imagePath !== '' && mb_strlen($imagePath) > self::IMAGE_PATH_MAX_LENGTH) {
            return 'Image path must be at most ' . self::IMAGE_PATH_MAX_LENGTH . ' characters.';
        }

        return null;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    public function setId(?int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }
    public function setName(string $v): self
    {
        $this->name = $v;
        return $this;
    }

    public function getIngredients(): string
    {
        return $this->ingredients;
    }
    public function setIngredients(string $v): self
    {
        $this->ingredients = $v;
        return $this;
    }

    public function getInstructions(): string
    {
        return $this->instructions;
    }
    public function setInstructions(string $v): self
    {
        $this->instructions = $v;
        return $this;
    }

    public function getImagePath(): ?string
    {
        return $this->imagePath;
    }
    public function setImagePath(?string $v): self
    {
        if ($v === null || trim($v) === '') {
            $this->imagePath = null;
        } else {
            $this->imagePath = trim($v);
        }
        return $this;
    }
}
