<?php

declare(strict_types=1);

namespace App\Data;

use App\Support\Format;
use InvalidArgumentException;
use Spatie\LaravelData\Data;

/** A subscription plan from config/plans.php, as used by views and services. */
final class PlanData extends Data
{
    /**
     * @param  array<string, int>  $limits
     * @param  list<string>  $features
     * @param  array<string, bool>  $featuresEnabled
     */
    public function __construct(
        public readonly string $slug,
        public readonly string $name,
        public readonly float $price,
        public readonly float $originalPrice,
        public readonly array $limits,
        public readonly array $features,
        public readonly array $featuresEnabled,
        public readonly string $description,
        public readonly ?string $stripePriceId,
    ) {}

    public static function fromConfig(string $slug): self
    {
        $plan = config("plans.{$slug}");

        if (!is_array($plan)) {
            throw new InvalidArgumentException("Unknown plan [{$slug}].");
        }

        return new self(
            slug: (string) $plan['slug'],
            name: (string) $plan['name'],
            price: (float) $plan['price'],
            originalPrice: (float) ($plan['original_price'] ?? 0),
            limits: array_map('intval', $plan['limits'] ?? []),
            features: array_values($plan['features'] ?? []),
            featuresEnabled: array_map('boolval', $plan['features_enabled'] ?? []),
            description: (string) ($plan['description'] ?? ''),
            stripePriceId: filled($plan['stripe_price_id'] ?? null) ? (string) $plan['stripe_price_id'] : null,
        );
    }

    /** @return array<string, self> Every configured plan, keyed by slug. */
    public static function catalog(): array
    {
        $plans = [];

        foreach (array_keys((array) config('plans')) as $slug) {
            $plans[$slug] = self::fromConfig((string) $slug);
        }

        return $plans;
    }

    public function discountPercent(): int
    {
        if ($this->originalPrice <= 0 || $this->price >= $this->originalPrice) {
            return 0;
        }

        return (int) floor((1 - $this->price / $this->originalPrice) * 100);
    }

    public function isFree(): bool
    {
        return $this->price <= 0;
    }

    public function formattedPrice(): string
    {
        return Format::money($this->price);
    }

    public function formattedOriginalPrice(): string
    {
        return Format::money($this->originalPrice);
    }

    /** Yearly saving against the reference price (0 when there is none). */
    public function formattedYearlySaving(): string
    {
        return Format::money(max(0, $this->originalPrice - $this->price) * 12);
    }

    public function limit(string $resource): int
    {
        return $this->limits[$resource] ?? -1;
    }

    public function hasFeature(string $feature): bool
    {
        return $this->featuresEnabled[$feature] ?? false;
    }
}
