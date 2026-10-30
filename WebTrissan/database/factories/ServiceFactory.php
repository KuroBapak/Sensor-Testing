<?php

namespace Database\Factories;

use App\Models\Service;
use App\Models\ServiceCluster;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->words(3, true);

        return [
            'service_cluster_id' => ServiceCluster::factory(),
            'service_title' => Str::title($title),
            'short_title' => Str::title($title),
            'slug' => Str::slug($title),
            'service_description' => fake()->sentence(),
            'sort_order' => fake()->numberBetween(1, 50),
        ];
    }

    /**
     * Give the service the body text, process steps and FAQ that make its landing page worth indexing.
     */
    public function withLandingContent(): static
    {
        return $this->state(fn (): array => [
            'landing_body' => collect(range(1, 3))
                ->map(fn (): string => fake()->paragraph(Service::MIN_BODY_WORDS))
                ->implode("\n\n"),
            'process_steps' => collect(range(1, Service::MIN_PROCESS_STEPS))
                ->map(fn (int $number): array => ['title' => "Tahap {$number}", 'description' => fake()->sentence(12)])
                ->all(),
            'faqs' => collect(range(1, Service::MIN_FAQS))
                ->map(fn (int $number): array => ['question' => "Pertanyaan {$number}?", 'answer' => fake()->sentence(14)])
                ->all(),
        ]);
    }
}
