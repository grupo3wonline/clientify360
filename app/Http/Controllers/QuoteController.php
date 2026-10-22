<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Deal;
use App\Models\FinancialEntry;
use App\Models\Quote;
use App\Services\QuoteService;
use App\Support\OwnedRule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

final class QuoteController extends Controller
{
    public function __construct(
        private readonly QuoteService $service,
    ) {}

    public function index(Request $request): View
    {
        $status = $request->query('status');
        $search = trim((string) $request->query('search', ''));

        // Expira automaticamente os vencidos ao listar (sem precisar de cron)
        Quote::whereIn('status', ['draft', 'sent'])->whereDate('valid_until', '<', now()->toDateString())->update(['status' => 'expired']);

        $quotes = Quote::with('client:id,name')
            ->when(array_key_exists((string) $status, Quote::STATUS_LABELS), fn ($q) => $q->where('status', $status))
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('number', 'like', "%{$search}%")
                ->orWhere('title', 'like', "%{$search}%")
                ->orWhereHas('client', fn ($c) => $c->where('name', 'like', "%{$search}%"))))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $totals = Quote::selectRaw('status, COUNT(*) as qty, SUM(total) as amount')->groupBy('status')->get()->keyBy('status');

        return view('quotes.index', compact('quotes', 'status', 'search', 'totals'));
    }

    public function create(Request $request): View
    {
        $quote = new Quote([
            'client_id' => $request->query('client_id'),
            'deal_id' => $request->query('deal_id'),
            'valid_until' => now()->addDays(15),
        ]);

        if ($quote->deal_id && ($deal = Deal::find($quote->deal_id))) {
            $quote->client_id = $deal->client_id;
            $quote->title = $deal->title;
        }

        return view('quotes.form', $this->formData($quote));
    }

    public function store(Request $request): RedirectResponse
    {
        [$data, $items] = $this->validated($request);
        $quote = $this->service->save(null, $data, $items);

        return redirect()->route('quotes.show', $quote)->with('success', "Orçamento {$quote->number} criado!");
    }

    public function show(Quote $quote): View
    {
        $quote->load(['items.product', 'client', 'deal', 'entries']);

        return view('quotes.show', [
            'quote' => $quote,
            'paymentMethods' => FinancialEntry::PAYMENT_METHODS,
        ]);
    }

    public function edit(Quote $quote): View|RedirectResponse
    {
        if (!$quote->isEditable()) {
            return redirect()->route('quotes.show', $quote)->with('error', 'Este orçamento já foi finalizado e não pode ser editado. Use "Duplicar".');
        }

        $quote->load('items');

        return view('quotes.form', $this->formData($quote));
    }

    public function update(Request $request, Quote $quote): RedirectResponse
    {
        [$data, $items] = $this->validated($request);

        try {
            $this->service->save($quote, $data, $items);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('quotes.show', $quote)->with('success', 'Orçamento atualizado!');
    }

    public function destroy(Quote $quote): RedirectResponse
    {
        if ($quote->status === 'approved') {
            return back()->with('error', 'Orçamentos aprovados geraram estoque e financeiro — não podem ser excluídos.');
        }

        $quote->delete();

        return redirect()->route('quotes.index')->with('success', 'Orçamento excluído.');
    }

    public function print(Quote $quote): View
    {
        $quote->load(['items', 'client']);

        return view('quotes.print', ['quote' => $quote, 'user' => auth()->user()]);
    }

    public function send(Quote $quote): RedirectResponse
    {
        return $this->run(fn () => $this->service->markSent($quote), 'Orçamento marcado como enviado.');
    }

    public function reject(Quote $quote): RedirectResponse
    {
        return $this->run(fn () => $this->service->reject($quote), 'Orçamento marcado como recusado.');
    }

    public function approve(Request $request, Quote $quote): RedirectResponse
    {
        $options = $request->validate([
            'installments' => ['required', 'integer', 'min:1', 'max:60'],
            'first_due_date' => ['required', 'date'],
            'payment_method' => ['nullable', Rule::in(array_keys(FinancialEntry::PAYMENT_METHODS))],
            'allow_negative_stock' => ['nullable', 'boolean'],
        ]);
        $options['allow_negative_stock'] = $request->boolean('allow_negative_stock');

        return $this->run(
            fn () => $this->service->approve($quote, $options),
            'Venda confirmada! Estoque baixado e contas a receber geradas.'
        );
    }

    public function duplicate(Quote $quote): RedirectResponse
    {
        $quote->load('items');
        $copy = $this->service->duplicate($quote);

        return redirect()->route('quotes.edit', $copy)->with('success', "Cópia criada: {$copy->number}");
    }

    private function run(callable $action, string $message): RedirectResponse
    {
        try {
            $action();
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', $message);
    }

    private function formData(Quote $quote): array
    {
        return [
            'quote' => $quote,
            'clients' => Client::orderBy('name')->get(['id', 'name']),
            'deals' => Deal::open()->with('client:id,name')->orderBy('title')->get(['id', 'title', 'client_id']),
            'items' => old('items', $quote->relationLoaded('items')
                ? $quote->items->map(fn ($i) => [
                    'product_id' => $i->product_id,
                    'description' => $i->description,
                    'quantity' => (float) $i->quantity,
                    'unit_price' => (float) $i->unit_price,
                ])->all()
                : []),
        ];
    }

    /** @return array{0: array<string, mixed>, 1: array<int, array<string, mixed>>} */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'client_id' => ['required', 'string', OwnedRule::exists('clients')],
            'deal_id' => ['nullable', 'string', OwnedRule::exists('deals')],
            'title' => ['required', 'string', 'max:255'],
            'valid_until' => ['nullable', 'date'],
            'payment_terms' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:3000'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1', 'max:200'],
            'items.*.product_id' => ['nullable', 'string', OwnedRule::exists('products')],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0', 'max:99999999'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0', 'max:9999999999'],
        ], [
            'client_id.required' => 'Selecione o cliente.',
            'title.required' => 'Dê um título ao orçamento.',
            'items.required' => 'Adicione pelo menos um item.',
            'items.min' => 'Adicione pelo menos um item.',
            'items.*.description.required' => 'Todo item precisa de descrição.',
            'items.*.quantity.gt' => 'A quantidade deve ser maior que zero.',
        ]);

        $items = $data['items'];
        unset($data['items']);

        return [$data, $items];
    }
}
