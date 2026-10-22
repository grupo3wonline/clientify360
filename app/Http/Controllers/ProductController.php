<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Catálogo de produtos/serviços com estoque — sistema-vendas-master + OSPOS. */
final class ProductController extends Controller
{
    public function __construct(
        private readonly StockService $stock,
    ) {}

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $filter = $request->query('filter');

        $products = Product::query()
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")))
            ->when($filter === 'low_stock', fn ($q) => $q->lowStock())
            ->when($filter === 'service', fn ($q) => $q->where('type', 'service'))
            ->when($filter === 'product', fn ($q) => $q->where('type', 'product'))
            ->when($filter === 'inactive', fn ($q) => $q->where('is_active', false), fn ($q) => $q->where('is_active', true))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total' => Product::where('is_active', true)->count(),
            'low_stock' => Product::lowStock()->where('is_active', true)->count(),
            'stock_value' => (float) Product::where('track_stock', true)->where('is_active', true)->get(['stock', 'cost'])
                ->sum(fn ($p) => (float) $p->stock * (float) ($p->cost ?? 0)),
        ];

        return view('products.index', compact('products', 'search', 'filter', 'stats'));
    }

    public function create(): View
    {
        return view('products.form', ['product' => new Product(['type' => 'product', 'unit' => 'un', 'is_active' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $initialStock = (float) ($data['stock'] ?? 0);
        unset($data['stock']);

        $product = Product::create($data + ['stock' => 0]);

        // Saldo inicial entra como movimentação, para o histórico começar correto
        if ($product->track_stock && $initialStock > 0) {
            $this->stock->move($product, 'in', $initialStock, 'Saldo inicial', $product->cost !== null ? (float) $product->cost : null);
        }

        return redirect()->route('products.show', $product)->with('success', 'Produto cadastrado com sucesso!');
    }

    public function show(Product $product): View
    {
        $movements = $product->movements()->paginate(15);

        return view('products.show', compact('product', 'movements'));
    }

    public function edit(Product $product): View
    {
        return view('products.form', compact('product'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validated($request);
        unset($data['stock']); // saldo só muda por movimentação

        $product->update($data);

        return redirect()->route('products.show', $product)->with('success', 'Produto atualizado!');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Produto excluído.');
    }

    /** Entrada (compra), saída manual ou ajuste de inventário. */
    public function movement(Request $request, Product $product): RedirectResponse
    {
        abort_unless($product->track_stock, 422, 'Este item não controla estoque.');

        $data = $request->validate([
            'type' => ['required', Rule::in(['in', 'out', 'adjust'])],
            'quantity' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'document' => ['nullable', 'string', 'max:60'],
            'reason' => ['nullable', 'string', 'max:255'],
        ], [
            'quantity.required' => 'Informe a quantidade.',
        ]);

        if ($data['type'] !== 'adjust' && (float) $data['quantity'] <= 0) {
            return back()->withErrors(['quantity' => 'A quantidade deve ser maior que zero.']);
        }

        $defaultReason = ['in' => 'Entrada / compra', 'out' => 'Saída manual', 'adjust' => 'Ajuste de inventário'][$data['type']];

        $this->stock->move(
            $product,
            $data['type'],
            (float) $data['quantity'],
            ($data['reason'] ?? null) ?: $defaultReason,
            isset($data['unit_cost']) ? (float) $data['unit_cost'] : null,
            $data['document'] ?? null,
        );

        return back()->with('success', 'Estoque atualizado. Saldo atual: '.rtrim(rtrim(number_format((float) $product->stock, 3, ',', '.'), '0'), ',')." {$product->unit}");
    }

    /** Busca usada pelo editor de orçamentos (autocomplete). */
    public function search(Request $request): JsonResponse
    {
        $term = trim((string) $request->query('q', ''));

        $products = Product::active()
            ->when($term !== '', fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', "%{$term}%")->orWhere('sku', 'like', "%{$term}%")))
            ->orderBy('name')
            ->limit(15)
            ->get();

        return response()->json($products->map(fn (Product $p) => [
            'id' => $p->id,
            'name' => $p->name,
            'sku' => $p->sku,
            'unit' => $p->unit,
            'price' => $p->salePrice(),
            'track_stock' => $p->track_stock,
            'stock' => (float) $p->stock,
        ]));
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(['product', 'service'])],
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:2000'],
            'unit' => ['required', Rule::in(array_keys(Product::UNITS))],
            'price' => ['required', 'numeric', 'min:0'],
            'promo_price' => ['nullable', 'numeric', 'min:0', 'lt:price'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'track_stock' => ['nullable', 'boolean'],
            'stock' => ['nullable', 'numeric', 'min:0'],
            'min_stock' => ['nullable', 'numeric', 'min:0'],
            'ncm' => ['nullable', 'string', 'max:10'],
            'supplier' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'O nome é obrigatório.',
            'price.required' => 'Informe o preço de venda.',
            'promo_price.lt' => 'O preço promocional deve ser menor que o preço normal.',
        ]);

        $data['track_stock'] = $data['type'] === 'product' && $request->boolean('track_stock');
        $data['is_active'] = $request->boolean('is_active');
        $data['min_stock'] = $data['min_stock'] ?? 0;

        return $data;
    }
}
