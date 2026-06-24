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
