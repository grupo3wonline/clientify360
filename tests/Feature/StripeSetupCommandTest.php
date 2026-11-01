<?php

declare(strict_types=1);

use App\Support\StripeClientFactory;
use Stripe\Collection;
use Stripe\Exception\ApiConnectionException;
use Stripe\Price;
use Stripe\Product;
use Stripe\SearchResult;
use Stripe\Service\PriceService;
use Stripe\Service\ProductService;
use Stripe\StripeClient;

/** Binds a factory whose client exposes mocked services: no request ever leaves the test. */
function fakeStripe(PriceService $prices, ProductService $products): void
{
    $client = new class(['api_key' => 'test_fake_key']) extends StripeClient
    {
        /** @var array<string, object> */
        public array $services = [];

        public function __get($name)
        {
            return $this->services[$name];
        }
    };
    $client->services = ['prices' => $prices, 'products' => $products];

    app()->instance(StripeClientFactory::class, new class($client) extends StripeClientFactory
    {
        public function __construct(private readonly StripeClient $client) {}

        public function make(): StripeClient
        {
            return $this->client;
        }
    });
}

beforeEach(function (): void {
    config(['cashier.secret' => 'test_fake_key']);
});

it('does not create a price when the lookup key already exists', function (): void {
    $prices = Mockery::mock(PriceService::class);
    $prices->shouldReceive('all')->once()
        ->with(Mockery::on(fn (array $p): bool => $p['lookup_keys'] === ['clientify360_professional_monthly_2990']))
        ->andReturn(Collection::constructFrom(['data' => [['id' => 'price_existing', 'object' => 'price']]]));
    $prices->shouldNotReceive('create');
    $products = Mockery::mock(ProductService::class);
    $products->shouldNotReceive('search', 'create');
    fakeStripe($prices, $products);

    $this->artisan('stripe:setup')
        ->expectsOutputToContain('Price already exists: price_existing')
        ->expectsOutputToContain('STRIPE_PROFESSIONAL_PRICE_ID=price_existing')
        ->doesntExpectOutputToContain('sk_')
        ->assertSuccessful();
});

it('creates exactly one 2990 BRL monthly price on the existing product', function (): void {
    $prices = Mockery::mock(PriceService::class);
    $prices->shouldReceive('all')->once()->andReturn(Collection::constructFrom(['data' => []]));
    $prices->shouldReceive('create')->once()
        ->with(Mockery::on(fn (array $p): bool => $p['product'] === 'prod_existing'
            && $p['unit_amount'] === 2990
            && $p['currency'] === 'brl'
            && $p['recurring'] === ['interval' => 'month']
            && $p['lookup_key'] === 'clientify360_professional_monthly_2990'
            && $p['metadata']['plan'] === 'professional'))
        ->andReturn(Price::constructFrom(['id' => 'price_new']));
    $products = Mockery::mock(ProductService::class);
    $products->shouldReceive('search')->once()
        ->andReturn(SearchResult::constructFrom(['data' => [['id' => 'prod_existing', 'object' => 'product']]]));
    $products->shouldNotReceive('create');
    fakeStripe($prices, $products);

    $this->artisan('stripe:setup')
        ->expectsOutputToContain('STRIPE_PROFESSIONAL_PRICE_ID=price_new')
        ->assertSuccessful();
});

it('creates the product when none is found', function (): void {
    $prices = Mockery::mock(PriceService::class);
    $prices->shouldReceive('all')->once()->andReturn(Collection::constructFrom(['data' => []]));
    $prices->shouldReceive('create')->once()
        ->with(Mockery::on(fn (array $p): bool => $p['product'] === 'prod_new'))
        ->andReturn(Price::constructFrom(['id' => 'price_new']));
    $products = Mockery::mock(ProductService::class);
    $products->shouldReceive('search')->once()->andReturn(SearchResult::constructFrom(['data' => []]));
    $products->shouldReceive('create')->once()
        ->with(Mockery::on(fn (array $p): bool => str_contains($p['description'], 'R$ 29,90')))
        ->andReturn(Product::constructFrom(['id' => 'prod_new']));
    fakeStripe($prices, $products);

    $this->artisan('stripe:setup')->assertSuccessful();
});

it('fails when Stripe returns an API error', function (): void {
    $prices = Mockery::mock(PriceService::class);
    $prices->shouldReceive('all')->once()->andThrow(new ApiConnectionException('Could not connect to Stripe.'));
    $prices->shouldNotReceive('create');
    fakeStripe($prices, Mockery::mock(ProductService::class));

    $this->artisan('stripe:setup')
        ->expectsOutputToContain('Stripe error: Could not connect to Stripe.')
        ->assertFailed();
});

it('makes no call on --dry-run', function (): void {
    $prices = Mockery::mock(PriceService::class);
    $prices->shouldNotReceive('all', 'create');
    fakeStripe($prices, Mockery::mock(ProductService::class));

    $this->artisan('stripe:setup --dry-run')->assertSuccessful();
});

it('fails without a configured secret', function (): void {
    config(['cashier.secret' => '']);

    $this->artisan('stripe:setup')->assertFailed();
});
