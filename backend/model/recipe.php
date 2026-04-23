<?php

namespace App\Entity; // Adjust the namespace to fit your project structure

use DateTimeInterface;
use DateTime;

class Recipe {
    
    private int $id;

    private string $name;

    private string $ingredients;

    private string $instructions;


    private ?string $imagePath = null;

    private ?DateTimeInterface $createdAt = null;

    
    public function __construct(string $name, string $ingredients, string $instructions)
    {
        $this->name = $name;
        $this->ingredients = $ingredients;
        $this->instructions = $instructions;
    }

    // --- Getters and Setters ---

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

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getIngredients(): string
    {
        return $this->ingredients;
    }

    public function setIngredients(string $ingredients): self
    {
        $this->ingredients = $ingredients;
        return $this;
    }

    public function getInstructions(): string
    {
        return $this->instructions;
    }

    public function setInstructions(string $instructions): self
    {
        $this->instructions = $instructions;
        return $this;
    }

    public function getImagePath(): ?string
    {
        return $this->imagePath;
    }

    public function setImagePath(?string $imagePath): self
    {
        $this->imagePath = $imagePath;
        return $this;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}