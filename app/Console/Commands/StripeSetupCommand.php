<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

final class StripeSetupCommand extends Command
{
    protected $signature = 'stripe:setup';

    protected $description = 'Create Stripe product and price for the Professional plan';

    public function handle(): int
    {
        $this->info('Setting up Stripe product and price...');

        $secret = config('cashier.secret');

        if (empty($secret)) {
            $this->error('STRIPE_SECRET is not configured in .env');

            return self::FAILURE;
        }

        // Create Product
        $this->info('Creating product...');
        $productResponse = Http::withBasicAuth($secret, '')
            ->withoutVerifying()
            ->asForm()
            ->post('https://api.stripe.com/v1/products', [
                'name' => 'Clientify360 Profissional',
                'description' => 'Plano Profissional - Clientes ilimitados, exportação de dados e suporte prioritário.',
                'metadata[app]' => 'clientify360',
            ]);

        if ($productResponse->failed()) {
            $this->error('Failed to create product: '.$productResponse->body());

            return self::FAILURE;
        }

        $productId = $productResponse->json('id');
        $this->info("Product created: {$productId}");

        // Create Price
        $this->info('Creating price...');
        $priceResponse = Http::withBasicAuth($secret, '')
            ->withoutVerifying()
            ->asForm()
            ->post('https://api.stripe.com/v1/prices', [
                'product' => $productId,
                'unit_amount' => 2990,
                'currency' => 'brl',
                'recurring[interval]' => 'month',
                'metadata[plan]' => 'professional',
            ]);

        if ($priceResponse->failed()) {
            $this->error('Failed to create price: '.$priceResponse->body());

            return self::FAILURE;
        }

        $priceId = $priceResponse->json('id');
        $this->info("Price created: {$priceId}");

        $this->newLine();
        $this->warn('Add this to your .env file:');
        $this->line("STRIPE_PROFESSIONAL_PRICE_ID={$priceId}");
        $this->newLine();
        $this->info('Stripe setup complete!');
        $this->info('Test card: 4242 4242 4242 4242, any future date, any CVC.');

        return self::SUCCESS;
    }
}
