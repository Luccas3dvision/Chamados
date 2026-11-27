<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Category;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            // Gera um nome fictício para categoria
            'name' => fake()->unique()->words(2, true),
        ];
    }
}