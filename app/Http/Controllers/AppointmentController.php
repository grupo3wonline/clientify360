<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Deal;
use App\Services\AppointmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/** Agenda/Calendário de agendamentos (reuniões, visitas, ligações, videochamadas). */
final class AppointmentController extends Controller
{
    public function __construct(
        private readonly AppointmentService $service,
    ) {}

    /** Visão de calendário (mês) com navegação server-side via ?month=&year=. */
    public function index(Request $request): View
    {
        $month = (int) $request->query('month', (string) now()->month);
        $year = (int) $request->query('year', (string) now()->year);
        $month = min(max($month, 1), 12);

        $reference = Carbon::create($year, $month, 1)->startOfDay();
        $start = $reference->copy()->startOfMonth();
        $end = $reference->copy()->endOfMonth();

        $appointments = Appointment::whereBetween('starts_at', [$start, $end->copy()->endOfDay()])
            ->with('client:id,name')
            ->orderBy('starts_at')
            ->get()
            ->groupBy(fn (Appointment $a): string => Carbon::parse($a->starts_at)->format('Y-m-d'));

        $counters = [
            'today' => Appointment::whereBetween('starts_at', [now()->startOfDay(), now()->endOfDay()])->count(),
            'week' => Appointment::whereBetween('starts_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'pending' => Appointment::where('status', Appointment::STATUS_PENDING)->count(),
        ];

        return view('appointments.index', [
            'appointments' => $appointments,
            'reference' => $reference,
            'counters' => $counters,
        ]);
    }

    public function create(): View
    {
        return view('appointments.create', $this->formData());
    }

    public function store(StoreAppointmentRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return redirect()->route('appointments.index')->with('success', 'Agendamento criado com sucesso!');
    }

    public function show(Appointment $appointment): View
    {
        $appointment->load(['client', 'deal']);

        return view('appointments.show', compact('appointment'));
    }

    public function edit(Appointment $appointment): View
    {
        return view('appointments.edit', $this->formData() + ['appointment' => $appointment]);
    }

    public function update(UpdateAppointmentRequest $request, Appointment $appointment): RedirectResponse
    {
        $this->service->update($appointment, $request->validated());

        return redirect()->route('appointments.show', $appointment)->with('success', 'Agendamento atualizado!');
    }

    public function destroy(Appointment $appointment): RedirectResponse
    {
        $appointment->delete();

        return redirect()->route('appointments.index')->with('success', 'Agendamento excluído.');
    }

    public function confirm(Appointment $appointment): RedirectResponse
    {
        $this->service->confirm($appointment);

        return back()->with('success', 'Agendamento confirmado!');
    }

    public function done(Appointment $appointment): RedirectResponse
    {
        $this->service->markDone($appointment);

        return back()->with('success', 'Agendamento concluído!');
    }

    public function cancel(Appointment $appointment): RedirectResponse
    {
        $this->service->cancel($appointment);

        return back()->with('success', 'Agendamento cancelado.');
    }

    /** @return array{clients: Collection<int, Client>, deals: Collection<int, Deal>} */
    private function formData(): array
    {
        return [
            'clients' => Client::orderBy('name')->get(['id', 'name']),
            'deals' => Deal::open()->orderBy('title')->get(['id', 'title']),
        ];
    }
}
