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

     protected $model = \App\Models\Product::class;
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(3, true),
            'description' => $this->faker->sentence(),
            'price' => $this->faker->randomFloat(2, 1000, 10000),
            'stock' => $this->faker->numberBetween(1, 100),
            'image_url' => $this->faker->imageUrl(640, 480, 'products'),
            'is_available' => $this->faker->boolean(),
            'origin' => $this->faker->randomElement(['importe', 'local']),
        ];
    }
}
