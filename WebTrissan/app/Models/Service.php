<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Service extends Model
{
    use HasFactory;

    /**
     * Minimum landing page content before the page is listed, linked and indexed; below it the page is thin.
     */
    public const MIN_BODY_WORDS = 200;

    public const MIN_PROCESS_STEPS = 3;

    public const MIN_FAQS = 3;

    protected $guarded = ['id'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'process_steps' => 'array',
            'faqs' => 'array',
        ];
    }

    /**
     * Get the landing page body split into paragraphs.
     *
     * @return Attribute<array<int, string>, never>
     */
    protected function landingParagraphs(): Attribute
    {
        return Attribute::get(fn (): array => array_values(array_filter(
            array_map('trim', preg_split('/\R{2,}/', (string) $this->landing_body) ?: []),
            fn (string $paragraph): bool => $paragraph !== ''
        )));
    }

    /**
     * Get the process steps that have a title, in the order entered in the CMS.
     *
     * @return Attribute<array<int, array{title: string, description: string}>, never>
     */
    protected function landingSteps(): Attribute
    {
        return Attribute::get(fn (): array => collect($this->process_steps ?? [])
            ->filter(fn (mixed $step): bool => is_array($step) && filled($step['title'] ?? null))
            ->map(fn (array $step): array => ['title' => (string) $step['title'], 'description' => (string) ($step['description'] ?? '')])
            ->values()
            ->all());
    }

    /**
     * Get the FAQ entries that have both a question and an answer.
     *
     * @return Attribute<array<int, array{question: string, answer: string}>, never>
     */
    protected function landingFaqs(): Attribute
    {
        return Attribute::get(fn (): array => collect($this->faqs ?? [])
            ->filter(fn (mixed $faq): bool => is_array($faq) && filled($faq['question'] ?? null) && filled($faq['answer'] ?? null))
            ->map(fn (array $faq): array => ['question' => (string) $faq['question'], 'answer' => (string) $faq['answer']])
            ->values()
            ->all());
    }

    /**
     * Whether the landing page has the body text, process steps and FAQ needed to be listed and indexed.
     */
    public function hasLandingContent(): bool
    {
        return Str::wordCount(implode(' ', $this->landing_paragraphs)) >= self::MIN_BODY_WORDS
            && count($this->landing_steps) >= self::MIN_PROCESS_STEPS
            && count($this->landing_faqs) >= self::MIN_FAQS;
    }

    /**
     * Get the meta description: the start of the real body text, falling back to the CMS one-liner.
     */
    public function metaDescription(): string
    {
        $text = $this->landing_paragraphs[0] ?? (string) $this->service_description;

        return Str::limit(trim((string) preg_replace('/\s+/', ' ', $text)), 154, '…');
    }

    /**
     * Get the public URL for this service: its landing page once it has content, otherwise the filtered works list.
     */
    public function publicUrl(): string
    {
        return $this->hasLandingContent()
            ? route('services.show', $this)
            : url('/works?category='.$this->slug);
    }

    /**
     * Get the route key used in public URLs.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Get the service cluster that owns this service.
     *
     * @return BelongsTo<ServiceCluster, $this>
     */
    public function serviceCluster(): BelongsTo
    {
        return $this->belongsTo(ServiceCluster::class);
    }

    /**
     * Get the works associated with this service.
     *
     * @return HasMany<Work, $this>
     */
    public function works(): HasMany
    {
        return $this->hasMany(Work::class)->orderBy('sort_order');
    }
}
