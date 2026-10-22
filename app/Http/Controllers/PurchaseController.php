<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StorePurchaseRequest;
use App\Models\Product;
use App\Models\Purchase;
use App\Services\PurchaseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

/** Compras — entrada de mercadoria e geração de contas a pagar ao fornecedor. */
final class PurchaseController extends Controller
{
    public function __construct(
        private readonly PurchaseService $service,
    ) {}

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $purchases = Purchase::withCount('items')
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('number', 'like', "%{$search}%")
                ->orWhere('supplier', 'like', "%{$search}%")
                ->orWhere('document_number', 'like', "%{$search}%")))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $summary = Purchase::selectRaw('COUNT(*) as qty, COALESCE(SUM(total), 0) as amount')->first();

        return view('purchases.index', compact('purchases', 'search', 'summary'));
    }

    public function create(): View
    {
        $products = Product::active()->orderBy('name')->get(['id', 'name', 'sku', 'unit', 'cost']);

        return view('purchases.create', compact('products'));
    }

    public function store(StorePurchaseRequest $request): RedirectResponse
    {
        $purchase = $this->service->create($request->validated());

        return redirect()->route('purchases.show', $purchase)->with('success', "Compra {$purchase->number} registrada!");
    }

    public function show(Purchase $purchase): View
    {
        $purchase->load('items.product');

        return view('purchases.show', ['purchase' => $purchase]);
    }

    public function confirm(Purchase $purchase): RedirectResponse
    {
        try {
            $this->service->confirm($purchase);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Compra confirmada: estoque atualizado e conta a pagar gerada.');
    }
}
