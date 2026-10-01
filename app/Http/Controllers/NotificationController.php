<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\DismissedNotification;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class NotificationController extends Controller
{
    public function __construct(
        private readonly NotificationService $service
    ) {}

    public function index(): View
    {
        $notifications = $this->service->for();

        return view('notifications.index', compact('notifications'));
    }

    /** Dispensa (marca como lida) uma notificação individual, persistindo a chave. */
    public function markAsRead(string $id): RedirectResponse
    {
        DismissedNotification::firstOrCreate(
            ['user_id' => auth()->id(), 'notification_key' => $id],
            ['dismissed_at' => now()],
        );

        return redirect()->route('notifications.index')
            ->with('success', 'Notificação dispensada.');
    }

    /** Dispensa todas as notificações atualmente visíveis. */
    public function markAllRead(Request $request): RedirectResponse
    {
        $keys = $request->input('keys', []);

        if (!is_array($keys)) {
            $keys = [];
        }

        foreach ($keys as $key) {
            if (is_string($key) && $key !== '') {
                DismissedNotification::firstOrCreate(
                    ['user_id' => $request->user()->id, 'notification_key' => $key],
                    ['dismissed_at' => now()],
                );
            }
        }

        return redirect()->route('notifications.index')
            ->with('success', 'Notificações dispensadas.');
    }
}
