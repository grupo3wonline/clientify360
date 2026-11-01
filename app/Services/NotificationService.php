<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Deal;
use App\Models\DismissedNotification;
use App\Models\FinancialEntry;
use App\Models\Interaction;
use App\Models\LeadFormSubmission;
use App\Models\Product;
use App\Models\Reminder;
use App\Support\Format;
use App\Traits\CachesPerAccount;
use Illuminate\Support\Collection;

/**
 * Gera as notificações em tempo real a partir dos dados do sistema
 * (lembretes, atendimentos, financeiro, estoque, leads e oportunidades).
 *
 * As notificações não são persistidas: têm uma chave estável e podem ser
 * dispensadas por usuário (ver {@see DismissedNotification}).
 */
final class NotificationService
{
    use CachesPerAccount;

    /** Cache por request para evitar recomputar (ex.: badge do menu + página). */
    private ?Collection $cache = null;

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function for(): Collection
    {
        if ($this->cache !== null) {
            return $this->cache;
        }

        $notifications = collect();

        $this->pushReminders($notifications);
        $this->pushInteractions($notifications);
        $this->pushFinance($notifications);
        $this->pushStock($notifications);
        $this->pushLeads($notifications);
        $this->pushDeals($notifications);

        $dismissed = DismissedNotification::where('user_id', auth()->id())
            ->pluck('notification_key')
            ->all();
        if ($dismissed !== []) {
            $notifications = $notifications->reject(
                fn (array $n): bool => in_array($n['id'], $dismissed, true)
            );
        }

        return $this->cache = $notifications->sortByDesc('time')->values();
    }

    /** Menu badge. Cached per user; any account write (incl. a dismissal) bumps the account version. */
    public function count(): int
    {
        if ($this->cache !== null) {
            return $this->cache->count();
        }

        return (int) $this->rememberForAccount('notifications:'.auth()->id(), 300, fn (): int => $this->for()->count());
    }

    private function pushReminders(Collection $notifications): void
    {
        $overdue = Reminder::where('is_completed', false)
            ->where('remind_at', '<', now())
            ->with('client')
            ->orderByDesc('remind_at')
            ->get();

        foreach ($overdue as $reminder) {
            $notifications->push([
                'id' => 'reminder-'.$reminder->id,
                'type' => 'overdue_reminder',
                'icon' => 'fa-solid fa-bell text-red-500',
                'title' => 'Lembrete atrasado',
                'message' => $reminder->title.($reminder->client ? ' — '.$reminder->client->name : ''),
                'time' => $reminder->remind_at,
                'url' => route('reminders.index', ['filter' => 'overdue']),
            ]);
        }

        $upcoming = Reminder::where('is_completed', false)
            ->whereBetween('remind_at', [now(), now()->addHours(24)])
            ->with('client')
            ->orderBy('remind_at')
            ->get();

        foreach ($upcoming as $reminder) {
            $notifications->push([
                'id' => 'upcoming-'.$reminder->id,
                'type' => 'upcoming_reminder',
                'icon' => 'fa-solid fa-clock text-amber-500',
                'title' => 'Lembrete em breve',
                'message' => $reminder->title.' — '.$reminder->remind_at->format('H:i'),
                'time' => $reminder->remind_at,
                'url' => route('reminders.index'),
            ]);
        }
    }

    private function pushInteractions(Collection $notifications): void
    {
        $stale = Interaction::where('status', 'open')
            ->where('created_at', '<', now()->subHours(48))
            ->with('client')
            ->get();

        foreach ($stale as $interaction) {
            $notifications->push([
                'id' => 'stale-'.$interaction->id,
                'type' => 'stale_interaction',
                'icon' => 'fa-solid fa-exclamation-triangle text-orange-500',
                'title' => 'Atendimento pendente',
                'message' => $interaction->subject.' — '.($interaction->client->name ?? 'Cliente'),
                'time' => $interaction->created_at,
                'url' => route('interactions.index', ['status' => 'open']),
            ]);
        }
    }

    private function pushFinance(Collection $notifications): void
    {
        $overdueEntries = FinancialEntry::overdue()->with('client:id,name')->orderBy('due_date')->limit(20)->get();

        foreach ($overdueEntries as $entry) {
            $receivable = $entry->direction === FinancialEntry::RECEIVABLE;
            $notifications->push([
                'id' => 'entry-'.$entry->id,
                'type' => 'overdue_entry',
                'icon' => $receivable ? 'fa-solid fa-hand-holding-dollar text-red-500' : 'fa-solid fa-file-invoice text-red-500',
                'title' => $receivable ? 'Recebimento em atraso' : 'Conta a pagar vencida',
                'message' => $entry->description.' — '.Format::money((float) $entry->amount).($entry->client ? ' ('.$entry->client->name.')' : ''),
                'time' => $entry->due_date,
                'url' => route('finance.entries', ['status' => 'overdue']),
            ]);
        }
    }

    private function pushStock(Collection $notifications): void
    {
        foreach (Product::lowStock()->where('is_active', true)->limit(20)->get() as $product) {
            $notifications->push([
                'id' => 'stock-'.$product->id,
                'type' => 'low_stock',
                'icon' => 'fa-solid fa-boxes-stacked text-amber-500',
                'title' => 'Estoque baixo',
                'message' => $product->name.' — saldo '.Format::qty($product->stock).' '.$product->unit.' (mínimo '.Format::qty($product->min_stock).')',
                'time' => $product->updated_at,
                'url' => route('products.show', $product),
            ]);
        }
    }

    private function pushLeads(Collection $notifications): void
    {
        $submissions = LeadFormSubmission::with(['form:id,name', 'client:id,name'])
            ->where('created_at', '>=', now()->subHours(48))
            ->latest()
            ->limit(20)
            ->get();

        foreach ($submissions as $submission) {
            $notifications->push([
                'id' => 'lead-'.$submission->id,
                'type' => 'new_lead',
                'icon' => 'fa-solid fa-magnet text-purple-500',
                'title' => 'Novo lead captado',
                'message' => ($submission->client?->name ?? $submission->data['name'] ?? 'Lead').' via '.($submission->form?->name ?? 'formulário'),
                'time' => $submission->created_at,
                'url' => $submission->client_id ? route('clients.show', $submission->client_id) : route('lead-forms.index'),
            ]);
        }
    }

    private function pushDeals(Collection $notifications): void
    {
        $deals = Deal::open()->with('client:id,name')
            ->whereDate('expected_close_date', '<', now()->toDateString())
            ->limit(20)
            ->get();

        foreach ($deals as $deal) {
            $notifications->push([
                'id' => 'deal-'.$deal->id,
                'type' => 'stale_deal',
                'icon' => 'fa-solid fa-handshake-slash text-orange-500',
                'title' => 'Oportunidade parada',
                'message' => $deal->title.' — previsão era '.$deal->expected_close_date->format('d/m'),
                'time' => $deal->expected_close_date,
                'url' => route('deals.show', $deal),
            ]);
        }
    }
}
