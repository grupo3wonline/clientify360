<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceOrderRequest;
use App\Http\Requests\UpdateServiceOrderRequest;
use App\Models\Client;
use App\Models\Quote;
use App\Models\ServiceOrder;
use App\Services\PdfService;
use App\Services\ServiceOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

/** Ordens de Serviço — acompanhamento de execução pós-venda. */
final class ServiceOrderController extends Controller
{
    public function __construct(
        private readonly ServiceOrderService $service,
    ) {}

    public function index(Request $request): View
    {
        $status = $this->queryString($request, 'status');
        $search = trim((string) $this->queryString($request, 'search', ''));

        $orders = ServiceOrder::with('client:id,name')
            ->when(array_key_exists((string) $status, ServiceOrder::STATUSES), fn ($q) => $q->where('status', $status))
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('number', 'like', "%{$search}%")
                ->orWhere('title', 'like', "%{$search}%")
                ->orWhereHas('client', fn ($c) => $c->where('name', 'like', "%{$search}%"))))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $totals = ServiceOrder::selectRaw('status, COUNT(*) as qty, SUM(total) as amount')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        return view('service-orders.index', compact('orders', 'status', 'search', 'totals'));
    }

    public function create(): View
    {
        return view('service-orders.create', $this->formData());
    }

    public function store(StoreServiceOrderRequest $request): RedirectResponse
    {
        $order = $this->service->create($request->validated());

        return redirect()->route('service-orders.show', $order)->with('success', "Ordem de serviço {$order->number} criada!");
    }

    public function show(ServiceOrder $serviceOrder): View
    {
        $serviceOrder->load(['client', 'quote']);

        return view('service-orders.show', ['order' => $serviceOrder]);
    }

    /** Plano Profissional (plan.feature:pdf). */
    public function pdf(Request $request, ServiceOrder $serviceOrder, PdfService $pdf): Response
    {
        $this->authorize('view', $serviceOrder);

        return $pdf->serviceOrder($serviceOrder, $request->user());
    }

    public function edit(ServiceOrder $serviceOrder): View
    {
        return view('service-orders.edit', $this->formData() + ['order' => $serviceOrder]);
    }

    public function update(UpdateServiceOrderRequest $request, ServiceOrder $serviceOrder): RedirectResponse
    {
        $this->service->update($serviceOrder, $request->validated());

        return redirect()->route('service-orders.show', $serviceOrder)->with('success', 'Ordem de serviço atualizada!');
    }

    public function destroy(ServiceOrder $serviceOrder): RedirectResponse
    {
        $serviceOrder->delete();

        return redirect()->route('service-orders.index')->with('success', 'Ordem de serviço excluída.');
    }

    public function start(ServiceOrder $serviceOrder): RedirectResponse
    {
        return $this->run(fn () => $this->service->start($serviceOrder), 'Ordem de serviço iniciada!');
    }

    public function complete(ServiceOrder $serviceOrder): RedirectResponse
    {
        return $this->run(fn () => $this->service->complete($serviceOrder), 'Ordem de serviço concluída!');
    }

    public function cancel(ServiceOrder $serviceOrder): RedirectResponse
    {
        return $this->run(fn () => $this->service->cancel($serviceOrder), 'Ordem de serviço cancelada.');
    }

    public function billing(ServiceOrder $serviceOrder): RedirectResponse
    {
        return $this->run(fn () => $this->service->generateReceivable($serviceOrder), 'Cobrança gerada no financeiro.');
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

    /** @return array{clients: Collection<int, Client>, quotes: Collection<int, Quote>} */
    private function formData(): array
    {
        return [
            'clients' => Client::orderBy('name')->get(['id', 'name']),
            'quotes' => Quote::orderByDesc('created_at')->get(['id', 'number', 'title']),
        ];
    }
}
