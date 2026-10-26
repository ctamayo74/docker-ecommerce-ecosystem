<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(15),
            'sku' => strtoupper(fake()->unique()->bothify('SKU-####-???')),
            'price' => fake()->randomFloat(2, 5, 300),
            'stock' => fake()->numberBetween(0, 100),
            'image_url' => fake()->imageUrl(640, 480, 'products'),
            'is_active' => true,
        ];
    }

    /**
     * Estado: producto sin stock disponible.
     */
    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => 0,
        ]);
    }

    /**
     * Estado: producto inactivo (no debe listarse públicamente).
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
