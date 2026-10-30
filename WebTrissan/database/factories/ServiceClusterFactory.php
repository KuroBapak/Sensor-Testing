<?php

namespace Database\Factories;

use App\Models\ServiceCluster;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceCluster>
 */
class ServiceClusterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cluster_name' => fake()->unique()->words(3, true),
            'sort_order' => fake()->numberBetween(1, 20),
        ];
    }
}
