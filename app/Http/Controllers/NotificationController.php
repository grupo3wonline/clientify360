<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Reminder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        // Generate real-time notifications from system data
        $notifications = collect();

        // Overdue reminders
        $overdueReminders = Reminder::where('is_completed', false)
            ->where('remind_at', '<', now())
            ->with('client')
            ->orderByDesc('remind_at')
            ->get();

        foreach ($overdueReminders as $reminder) {
            $notifications->push([
                'id' => 'reminder-' . $reminder->id,
                'type' => 'overdue_reminder',
                'icon' => 'fa-solid fa-bell text-red-500',
                'title' => 'Lembrete atrasado',
                'message' => $reminder->title . ($reminder->client ? ' — ' . $reminder->client->name : ''),
                'time' => $reminder->remind_at,
                'url' => route('reminders.index', ['filter' => 'overdue']),
            ]);
        }

        // Upcoming reminders (next 24h)
        $upcomingReminders = Reminder::where('is_completed', false)
            ->whereBetween('remind_at', [now(), now()->addHours(24)])
            ->with('client')
            ->orderBy('remind_at')
            ->get();

        foreach ($upcomingReminders as $reminder) {
            $notifications->push([
                'id' => 'upcoming-' . $reminder->id,
                'type' => 'upcoming_reminder',
                'icon' => 'fa-solid fa-clock text-amber-500',
                'title' => 'Lembrete em breve',
                'message' => $reminder->title . ' — ' . $reminder->remind_at->format('H:i'),
                'time' => $reminder->remind_at,
                'url' => route('reminders.index'),
            ]);
        }

        // Open interactions without activity for 48h+
        $staleInteractions = \App\Models\Interaction::where('status', 'open')
            ->where('created_at', '<', now()->subHours(48))
            ->with('client')
            ->get();

        foreach ($staleInteractions as $interaction) {
            $notifications->push([
                'id' => 'stale-' . $interaction->id,
                'type' => 'stale_interaction',
                'icon' => 'fa-solid fa-exclamation-triangle text-orange-500',
                'title' => 'Atendimento pendente',
                'message' => $interaction->subject . ' — ' . ($interaction->client->name ?? 'Cliente'),
                'time' => $interaction->created_at,
                'url' => route('interactions.index', ['status' => 'open']),
            ]);
        }

        // Contas a receber/pagar vencidas (módulo Financeiro)
        $overdueEntries = \App\Models\FinancialEntry::overdue()->with('client:id,name')->orderBy('due_date')->limit(20)->get();

        foreach ($overdueEntries as $entry) {
            $receivable = $entry->direction === \App\Models\FinancialEntry::RECEIVABLE;
            $notifications->push([
                'id' => 'entry-' . $entry->id,
                'type' => 'overdue_entry',
                'icon' => $receivable ? 'fa-solid fa-hand-holding-dollar text-red-500' : 'fa-solid fa-file-invoice text-red-500',
                'title' => $receivable ? 'Recebimento em atraso' : 'Conta a pagar vencida',
                'message' => $entry->description . ' — R$ ' . number_format((float) $entry->amount, 2, ',', '.') . ($entry->client ? ' (' . $entry->client->name . ')' : ''),
                'time' => $entry->due_date,
                'url' => route('finance.entries', ['status' => 'overdue']),
            ]);
        }

        // Produtos abaixo do estoque mínimo
        foreach (\App\Models\Product::lowStock()->where('is_active', true)->limit(20)->get() as $product) {
            $notifications->push([
                'id' => 'stock-' . $product->id,
                'type' => 'low_stock',
                'icon' => 'fa-solid fa-boxes-stacked text-amber-500',
                'title' => 'Estoque baixo',
                'message' => $product->name . ' — saldo ' . \App\Support\Format::qty($product->stock) . ' ' . $product->unit . ' (mínimo ' . \App\Support\Format::qty($product->min_stock) . ')',
                'time' => $product->updated_at,
                'url' => route('products.show', $product),
            ]);
        }

        // Leads captados nas últimas 48h
        foreach (\App\Models\LeadFormSubmission::with(['form:id,name', 'client:id,name'])->where('created_at', '>=', now()->subHours(48))->latest()->limit(20)->get() as $submission) {
            $notifications->push([
                'id' => 'lead-' . $submission->id,
                'type' => 'new_lead',
                'icon' => 'fa-solid fa-magnet text-purple-500',
                'title' => 'Novo lead captado',
                'message' => ($submission->client?->name ?? $submission->data['name'] ?? 'Lead') . ' via ' . ($submission->form?->name ?? 'formulário'),
                'time' => $submission->created_at,
                'url' => $submission->client_id ? route('clients.show', $submission->client_id) : route('lead-forms.index'),
            ]);
        }

        // Oportunidades abertas com previsão de fechamento vencida
        foreach (\App\Models\Deal::open()->with('client:id,name')->whereDate('expected_close_date', '<', now()->toDateString())->limit(20)->get() as $deal) {
            $notifications->push([
                'id' => 'deal-' . $deal->id,
                'type' => 'stale_deal',
                'icon' => 'fa-solid fa-handshake-slash text-orange-500',
                'title' => 'Oportunidade parada',
                'message' => $deal->title . ' — previsão era ' . $deal->expected_close_date->format('d/m'),
                'time' => $deal->expected_close_date,
                'url' => route('deals.show', $deal),
            ]);
        }

        // Sort by time descending
        $notifications = $notifications->sortByDesc('time')->values();

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead(string $id): RedirectResponse
    {
        return redirect()->route('notifications.index');
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return redirect()->route('notifications.index')
            ->with('success', 'Notificações marcadas como lidas.');
    }
}
