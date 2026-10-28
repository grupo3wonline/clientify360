<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Support\StripeClientFactory;
use Illuminate\Console\Command;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;
use Throwable;

/**
 * Creates (once) the monthly R$ 29,90 price of the Professional plan.
 * Idempotent: the price is found by its lookup_key, so re-runs create nothing.
 * Older prices are never archived (existing subscriptions keep working).
 */
final class StripeSetupCommand extends Command
{
    public const LOOKUP_KEY = 'clientify360_professional_monthly_2990';

    private const UNIT_AMOUNT = 2990;

    private const PRODUCT = [
        'name' => 'Clientify360 Profissional',
        'description' => 'Plano Profissional (R$ 29,90/mês): clientes ilimitados, IA, exportação, PDFs e API.',
        'metadata' => ['app' => 'clientify360'],
    ];

    protected $signature = 'stripe:setup {--dry-run : Show the planned Stripe calls without making them}';

    protected $description = 'Create the Stripe price (R$ 29,90/month) for the Professional plan';

    public function handle(StripeClientFactory $factory): int
    {
        if (blank(config('cashier.secret'))) {
            $this->error('STRIPE_SECRET is not configured in .env');

            return self::FAILURE;
        }

        if ($this->option('dry-run')) {
            $this->line('[dry-run] prices.all lookup_keys=['.self::LOOKUP_KEY.']');
            $this->line("[dry-run] products.search metadata['app']:'clientify360' (fallback products.all)");
            $this->line('[dry-run] prices.create unit_amount='.self::UNIT_AMOUNT.' currency=brl interval=month (only if missing)');

            return self::SUCCESS;
        }

        try {
            $stripe = $factory->make();

            $existing = $stripe->prices->all(['lookup_keys' => [self::LOOKUP_KEY], 'active' => true, 'limit' => 1]);

            if (count($existing->data) > 0) {
                $this->info('Price already exists: '.$existing->data[0]->id);
                $this->line('STRIPE_PROFESSIONAL_PRICE_ID='.$existing->data[0]->id);

                return self::SUCCESS;
            }

            $price = $stripe->prices->create([
                'product' => $this->productId($stripe),
                'unit_amount' => self::UNIT_AMOUNT,
                'currency' => 'brl',
                'recurring' => ['interval' => 'month'],
                'lookup_key' => self::LOOKUP_KEY,
                'nickname' => 'Profissional mensal R$ 29,90',
                'metadata' => ['plan' => 'professional', 'app' => 'clientify360'],
            ]);
        } catch (ApiErrorException $e) {
            // Stripe error messages never contain the API key.
            $this->error('Stripe error: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info('Price created: '.$price->id);
        $this->line('STRIPE_PROFESSIONAL_PRICE_ID='.$price->id);

        return self::SUCCESS;
    }

    private function productId(StripeClient $stripe): string
    {
        try {
            $found = $stripe->products->search(['query' => "metadata['app']:'clientify360' AND active:'true'"])->data[0] ?? null;
        } catch (Throwable) {
            $found = collect($stripe->products->all(['limit' => 100])->data)
                ->first(fn ($product): bool => ($product->metadata['app'] ?? null) === 'clientify360' && $product->active);
        }

        if ($found !== null) {
            $this->info('Using product: '.$found->id);

            return (string) $found->id;
        }

        $product = $stripe->products->create(self::PRODUCT);
        $this->info('Product created: '.$product->id);

        return (string) $product->id;
    }
}
