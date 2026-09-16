<?php

declare(strict_types=1);

use App\Http\Controllers\ClientController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TermsController;
use Illuminate\Support\Facades\Route;

// Public page (Landing / Hotsite — página única de vendas)
Route::middleware('throttle:60,1')->group(function (): void {
    Route::get('/', [PageController::class, 'home'])->name('home');

    Route::get('/politica-de-privacidade', [PageController::class, 'privacy'])->name('privacy');
    Route::get('/sitemap.xml', [PageController::class, 'sitemap'])->name('sitemap');
    Route::get('/robots.txt', [PageController::class, 'robots'])->name('robots');

    // Rotas antigas → redirecionam para as âncoras da página única (preserva SEO/links).
    Route::permanentRedirect('/sobre', '/#sobre')->name('about');
    Route::permanentRedirect('/servicos', '/#servicos')->name('services');
    Route::permanentRedirect('/ferramentas', '/#ferramentas')->name('tools');
    Route::get('/contato', fn () => redirect('/#contato', 301))->name('contact');
});

// Dashboard (authenticated)
Route::get('/dashboard', function () {
    $user = auth()->user();
    $subscriptionService = app(\App\Services\SubscriptionService::class);
    $clientCount = app(\App\Services\ClientService::class)->countAll();
    $currentPlan = $subscriptionService->getCurrentPlan($user);
    $clientLimit = $subscriptionService->getClientLimit($user);

    return view('dashboard', compact('clientCount', 'currentPlan', 'clientLimit'));
})->middleware(['auth', 'verified', 'terms.accepted', 'throttle:120,1'])->name('dashboard');

// Authenticated routes
Route::middleware(['auth', 'verified', 'terms.accepted', 'throttle:120,1'])->group(function (): void {
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Billing / Subscription
    Route::get('/billing', [\App\Http\Controllers\BillingController::class, 'index'])->name('billing.index');
    Route::post('/billing/subscribe', [\App\Http\Controllers\BillingController::class, 'subscribe'])->name('billing.subscribe');
    Route::post('/billing/cancel', [\App\Http\Controllers\BillingController::class, 'cancel'])->name('billing.cancel');
    Route::post('/billing/resume', [\App\Http\Controllers\BillingController::class, 'resume'])->name('billing.resume');
    Route::get('/billing/portal', [\App\Http\Controllers\BillingController::class, 'portal'])->name('billing.portal');

    // Clients CRUD
    Route::resource('clients', ClientController::class)->except(['create', 'store']);
    Route::get('clients/create', [ClientController::class, 'create'])->middleware('plan.limit')->name('clients.create');
    Route::post('clients', [ClientController::class, 'store'])->middleware('plan.limit')->name('clients.store');

    // Reminders / Agenda
    Route::get('reminders', [\App\Http\Controllers\ReminderController::class, 'index'])->name('reminders.index');
    Route::post('reminders', [\App\Http\Controllers\ReminderController::class, 'store'])->name('reminders.store');
    Route::patch('reminders/{reminder}/complete', [\App\Http\Controllers\ReminderController::class, 'complete'])->name('reminders.complete');
    Route::delete('reminders/{reminder}', [\App\Http\Controllers\ReminderController::class, 'destroy'])->name('reminders.destroy');

    // Interactions / Support
    Route::get('interactions', [\App\Http\Controllers\InteractionController::class, 'index'])->name('interactions.index');
    Route::post('interactions', [\App\Http\Controllers\InteractionController::class, 'store'])->name('interactions.store');
    Route::patch('interactions/{interaction}/status', [\App\Http\Controllers\InteractionController::class, 'updateStatus'])->name('interactions.updateStatus');
    Route::delete('interactions/{interaction}', [\App\Http\Controllers\InteractionController::class, 'destroy'])->name('interactions.destroy');

    // Reports
    Route::get('reports', [\App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');

    // Notifications
    Route::get('notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/mark-read/{id}', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.markRead');
    Route::post('notifications/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllRead'])->name('notifications.markAllRead');

    // AI Analysis (Professional plan only)
    Route::get('ai/settings', [\App\Http\Controllers\AiController::class, 'settings'])->name('ai.settings');
    Route::post('ai/settings', [\App\Http\Controllers\AiController::class, 'saveSettings'])->name('ai.saveSettings');
    Route::post('ai/analyze/{client}', [\App\Http\Controllers\AiController::class, 'analyze'])->name('ai.analyze');

    // Export (Professional plan only)
    Route::get('/export/clients', [\App\Http\Controllers\ExportController::class, 'clients'])->name('export.clients');
});

// Terms of Service (requires auth but not terms acceptance)
Route::middleware(['auth'])->group(function (): void {
    Route::get('/termos-de-uso', [TermsController::class, 'show'])->name('terms.show');
    Route::post('/termos-de-uso', [TermsController::class, 'accept'])->name('terms.accept');
});

require __DIR__.'/auth.php';

// Stripe Webhook (no CSRF, no auth)
Route::post('/stripe/webhook', [\Laravel\Cashier\Http\Controllers\WebhookController::class, 'handleWebhook'])
    ->name('cashier.webhook');
