<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\FinancialCategory;
use App\Models\FinancialEntry;
use App\Services\FinanceService;
use App\Support\OwnedRule;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Contas a pagar/receber + fluxo de caixa (CrmPlus + sistema-vendas contas_pagar/boletos). */
final class FinanceController extends Controller
{
    public function __construct(
        private readonly FinanceService $finance,
    ) {}

    public function index(): View
    {
        FinancialCategory::ensureDefaultsFor((string) auth()->user()->account_id);

        $summary = $this->finance->monthSummary();
        $cashFlow = $this->finance->cashFlow();

        $upcoming = FinancialEntry::pending()
            ->with(['client:id,name', 'category:id,name,color'])
            ->whereDate('due_date', '<=', now()->addDays(7)->toDateString())
            ->orderBy('due_date')
            ->limit(10)
            ->get();

        $byCategory = FinancialEntry::payable()->where('status', 'paid')
            ->where('paid_at', '>=', now()->startOfMonth()->toDateString())
            ->with('category:id,name,color')
            ->get()
            ->groupBy(fn ($e) => $e->category?->name ?? 'Sem categoria')
            ->map(fn ($g) => ['total' => (float) $g->sum('paid_amount'), 'color' => $g->first()->category?->color ?? '#94a3b8'])
            ->sortByDesc('total');

        return view('finance.index', compact('summary', 'cashFlow', 'upcoming', 'byCategory'));
    }

    public function entries(Request $request): View
    {
        FinancialCategory::ensureDefaultsFor((string) auth()->user()->account_id);

        $direction = in_array($request->query('direction'), ['receivable', 'payable'], true) ? $request->query('direction') : null;
        $status = $request->query('status');
        $month = $request->query('month', now()->format('Y-m'));

        try {
            $ref = Carbon::createFromFormat('Y-m', (string) $month)->startOfMonth();
        } catch (\Throwable) {
            $ref = now()->startOfMonth();
        }

        $entries = FinancialEntry::with(['client:id,name', 'category:id,name,color', 'quote:id,number'])
            ->when($direction, fn ($q) => $q->where('direction', $direction))
            ->when($status === 'overdue', fn ($q) => $q->overdue())
            ->when(in_array($status, ['pending', 'paid', 'canceled'], true), fn ($q) => $q->where('status', $status))
            // "Atrasados" ignora o mês: mostra tudo que venceu
            ->when($status !== 'overdue', fn ($q) => $q->whereBetween('due_date', [$ref->toDateString(), $ref->copy()->endOfMonth()->toDateString()]))
            ->orderBy('due_date')
            ->paginate(25)
            ->withQueryString();

        $categories = FinancialCategory::orderBy('type')->orderBy('name')->get();
        $clients = Client::orderBy('name')->get(['id', 'name']);
        $paymentMethods = FinancialEntry::PAYMENT_METHODS;

        return view('finance.entries', compact('entries', 'direction', 'status', 'ref', 'categories', 'clients', 'paymentMethods'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $installments = (int) ($data['installments'] ?? 1);
        unset($data['installments']);

        $created = $this->finance->createInstallments($data, $installments);

        return back()->with('success', $created->count() > 1
            ? "{$created->count()} parcelas lançadas!"
            : 'Lançamento criado!');
    }

    public function update(Request $request, FinancialEntry $entry): RedirectResponse
    {
        $data = $this->validated($request, false);
        $entry->update($data);

        return back()->with('success', 'Lançamento atualizado!');
    }

    public function settle(Request $request, FinancialEntry $entry): RedirectResponse
    {
        $data = $request->validate([
            'paid_at' => ['nullable', 'date', 'before_or_equal:today'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['nullable', Rule::in(array_keys(FinancialEntry::PAYMENT_METHODS))],
        ]);

        $this->finance->settle(
            $entry,
            $data['paid_at'] ?? null,
            isset($data['paid_amount']) ? (float) $data['paid_amount'] : null,
            $data['payment_method'] ?? null,
        );

        return back()->with('success', $entry->direction === 'receivable' ? 'Recebimento registrado!' : 'Pagamento registrado!');
    }

    public function reopen(FinancialEntry $entry): RedirectResponse
    {
        $this->finance->reopen($entry);

        return back()->with('success', 'Lançamento reaberto.');
    }

    public function cancel(FinancialEntry $entry): RedirectResponse
    {
        $entry->update(['status' => 'canceled']);

        return back()->with('success', 'Lançamento cancelado.');
    }

    public function destroy(FinancialEntry $entry): RedirectResponse
    {
        $entry->delete();

        return back()->with('success', 'Lançamento excluído.');
    }

    public function categories(): View
    {
        FinancialCategory::ensureDefaultsFor((string) auth()->user()->account_id);
        $categories = FinancialCategory::withCount('entries')->orderBy('type')->orderBy('name')->get();

        return view('finance.categories', compact('categories'));
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        FinancialCategory::create($request->validate([
            'name' => ['required', 'string', 'max:80'],
            'type' => ['required', Rule::in(['income', 'expense'])],
            'color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ], ['name.required' => 'Informe o nome da categoria.']));

        return back()->with('success', 'Categoria criada!');
    }

    public function destroyCategory(FinancialCategory $category): RedirectResponse
    {
        $category->delete(); // lançamentos mantêm o vínculo (soft delete) para o histórico

        return back()->with('success', 'Categoria excluída.');
    }

    private function validated(Request $request, bool $creating = true): array
    {
        $rules = [
            'direction' => ['required', Rule::in(['receivable', 'payable'])],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'gt:0', 'max:9999999999'],
            'due_date' => ['required', 'date'],
            'category_id' => ['nullable', 'string', OwnedRule::exists('financial_categories')],
            'client_id' => ['nullable', 'string', OwnedRule::exists('clients')],
            'counterparty' => ['nullable', 'string', 'max:255'],
            'payment_method' => ['nullable', Rule::in(array_keys(FinancialEntry::PAYMENT_METHODS))],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];

        if ($creating) {
            $rules['installments'] = ['nullable', 'integer', 'min:1', 'max:60'];
        }

        return $request->validate($rules, [
            'description.required' => 'Descreva o lançamento.',
            'amount.required' => 'Informe o valor.',
            'amount.gt' => 'O valor deve ser maior que zero.',
            'due_date.required' => 'Informe o vencimento.',
        ]);
    }
}
