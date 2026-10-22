<?php

declare(strict_types=1);

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AiController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DealController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\InteractionController;
use App\Http\Controllers\LeadFormController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PipelineStageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicLeadFormController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\ReminderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ServiceOrderController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TermsController;
use Illuminate\Support\Facades\Route;
use Laravel\Cashier\Http\Controllers\WebhookController;

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
// Dashboard → painel Filament (/app) é a home autenticada. A rota é mantida
// porque o Breeze (verificação de e-mail etc.) e vários links apontam para ela.
Route::get('/dashboard', fn () => redirect('/app'))
    ->middleware(['auth', 'throttle:120,1'])->name('dashboard');

// Authenticated routes
Route::middleware(['auth', 'verified', 'terms.accepted', 'throttle:120,1'])->group(function (): void {
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/profile/exportar-dados', [ProfileController::class, 'exportPersonalData'])->name('profile.export');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Equipe / Colaboradores (ACL: permissão account.members)
    Route::get('/equipe', [TeamController::class, 'index'])->name('team.index');
    Route::post('/equipe/convites', [TeamController::class, 'invite'])->name('team.invite');
    Route::patch('/equipe/membros/{member}/papel', [TeamController::class, 'updateRole'])->name('team.updateRole');
    Route::delete('/equipe/membros/{member}', [TeamController::class, 'removeMember'])->name('team.removeMember');
    Route::delete('/equipe/convites/{invitation}', [TeamController::class, 'revokeInvite'])->name('team.revokeInvite');

    // Billing / Subscription (apenas quem tem permissão de cobrança — Dono)
    Route::middleware('permission:account.billing')->group(function (): void {
        Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
        Route::post('/billing/subscribe', [BillingController::class, 'subscribe'])->name('billing.subscribe');
        Route::post('/billing/cancel', [BillingController::class, 'cancel'])->name('billing.cancel');
        Route::post('/billing/resume', [BillingController::class, 'resume'])->name('billing.resume');
        Route::get('/billing/portal', [BillingController::class, 'portal'])->name('billing.portal');
    });

    // Clients CRUD
    // create/store ANTES do resource: senão "clients/{client}" captura "clients/create" (404)
    Route::get('clients/create', [ClientController::class, 'create'])->middleware('plan.limit')->name('clients.create');
    Route::post('clients', [ClientController::class, 'store'])->middleware('plan.limit')->name('clients.store');
    Route::resource('clients', ClientController::class)->except(['create', 'store']);

    // Reminders / Agenda
    Route::get('reminders', [ReminderController::class, 'index'])->name('reminders.index');
    Route::post('reminders', [ReminderController::class, 'store'])->name('reminders.store');
    Route::patch('reminders/{reminder}/complete', [ReminderController::class, 'complete'])->name('reminders.complete');
    Route::delete('reminders/{reminder}', [ReminderController::class, 'destroy'])->name('reminders.destroy');

    // Appointments / Calendário
    // create/store ANTES do resource: senão "appointments/{appointment}" captura "appointments/create".
    // Escrita exige appointments.manage; leitura exige appointments.view.
    Route::middleware('permission:appointments.manage')->group(function (): void {
        Route::get('appointments/create', [AppointmentController::class, 'create'])->name('appointments.create');
        Route::post('appointments', [AppointmentController::class, 'store'])->name('appointments.store');
        Route::resource('appointments', AppointmentController::class)->only(['edit', 'update', 'destroy']);
        Route::patch('appointments/{appointment}/confirm', [AppointmentController::class, 'confirm'])->name('appointments.confirm');
        Route::patch('appointments/{appointment}/done', [AppointmentController::class, 'done'])->name('appointments.done');
        Route::patch('appointments/{appointment}/cancel', [AppointmentController::class, 'cancel'])->name('appointments.cancel');
    });
    Route::middleware('permission:appointments.view')->group(function (): void {
        Route::resource('appointments', AppointmentController::class)->only(['index', 'show']);
    });

    // Service Orders / Ordens de Serviço
    // create/store ANTES do resource: senão "service-orders/{service_order}" captura "service-orders/create".
    // Escrita exige service_orders.manage; leitura exige service_orders.view.
    Route::middleware('permission:service_orders.manage')->group(function (): void {
        Route::get('service-orders/create', [ServiceOrderController::class, 'create'])->name('service-orders.create');
        Route::post('service-orders', [ServiceOrderController::class, 'store'])->name('service-orders.store');
        Route::resource('service-orders', ServiceOrderController::class)->only(['edit', 'update', 'destroy']);
        Route::patch('service-orders/{service_order}/start', [ServiceOrderController::class, 'start'])->name('service-orders.start');
        Route::patch('service-orders/{service_order}/complete', [ServiceOrderController::class, 'complete'])->name('service-orders.complete');
        Route::patch('service-orders/{service_order}/cancel', [ServiceOrderController::class, 'cancel'])->name('service-orders.cancel');
        Route::patch('service-orders/{service_order}/billing', [ServiceOrderController::class, 'billing'])->name('service-orders.billing');
    });
    Route::middleware('permission:service_orders.view')->group(function (): void {
        Route::resource('service-orders', ServiceOrderController::class)->only(['index', 'show']);
    });

    // Purchases / Compras (entrada de mercadoria + conta a pagar ao fornecedor)
    // create/store ANTES do resource: senão "purchases/{purchase}" captura "purchases/create".
    // Escrita exige purchases.manage; leitura exige purchases.view.
    Route::middleware('permission:purchases.manage')->group(function (): void {
        Route::get('purchases/create', [PurchaseController::class, 'create'])->name('purchases.create');
        Route::post('purchases', [PurchaseController::class, 'store'])->name('purchases.store');
        Route::patch('purchases/{purchase}/confirm', [PurchaseController::class, 'confirm'])->name('purchases.confirm');
    });
    Route::middleware('permission:purchases.view')->group(function (): void {
        Route::resource('purchases', PurchaseController::class)->only(['index', 'show']);
    });

    // Interactions / Support
    Route::get('interactions', [InteractionController::class, 'index'])->name('interactions.index');
    Route::post('interactions', [InteractionController::class, 'store'])->name('interactions.store');
    Route::patch('interactions/{interaction}/status', [InteractionController::class, 'updateStatus'])->name('interactions.updateStatus');
    Route::delete('interactions/{interaction}', [InteractionController::class, 'destroy'])->name('interactions.destroy');

    // Reports
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');

    // Notifications
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/mark-read/{id}', [NotificationController::class, 'markAsRead'])->name('notifications.markRead');
    Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.markAllRead');

    // AI Analysis (Professional plan only)
    Route::get('ai/settings', [AiController::class, 'settings'])->name('ai.settings');
    Route::post('ai/settings', [AiController::class, 'saveSettings'])->name('ai.saveSettings');
    Route::post('ai/analyze/{client}', [AiController::class, 'analyze'])->name('ai.analyze');

    // Export (Professional plan only)
    Route::get('/export/clients', [ExportController::class, 'clients'])->name('export.clients');
    Route::get('/export/clients/xlsx', [ExportController::class, 'clientsExcel'])->name('export.clients.xlsx');

    // ===== CRM comercial (CrmPlus + crm-master) =====
    Route::get('pipeline', [DealController::class, 'index'])->name('deals.index');
    Route::get('deals', [DealController::class, 'list'])->name('deals.list');
    Route::post('deals', [DealController::class, 'store'])->name('deals.store');
    Route::get('deals/{deal}', [DealController::class, 'show'])->name('deals.show');
    Route::put('deals/{deal}', [DealController::class, 'update'])->name('deals.update');
    Route::delete('deals/{deal}', [DealController::class, 'destroy'])->name('deals.destroy');
    Route::patch('deals/{deal}/move', [DealController::class, 'move'])->name('deals.move');
    Route::post('deals/{deal}/win', [DealController::class, 'win'])->name('deals.win');
    Route::post('deals/{deal}/lose', [DealController::class, 'lose'])->name('deals.lose');
    Route::post('deals/{deal}/reopen', [DealController::class, 'reopen'])->name('deals.reopen');

    Route::get('pipeline/stages', [PipelineStageController::class, 'index'])->name('stages.index');
    Route::post('pipeline/stages', [PipelineStageController::class, 'store'])->name('stages.store');
    Route::put('pipeline/stages/{stage}', [PipelineStageController::class, 'update'])->name('stages.update');
    Route::delete('pipeline/stages/{stage}', [PipelineStageController::class, 'destroy'])->name('stages.destroy');

    Route::get('activities', [ActivityController::class, 'index'])->name('activities.index');
    Route::post('activities', [ActivityController::class, 'store'])->name('activities.store');
    Route::delete('activities/{activity}', [ActivityController::class, 'destroy'])->name('activities.destroy');

    Route::get('tags', [TagController::class, 'index'])->name('tags.index');
    Route::post('tags', [TagController::class, 'store'])->name('tags.store');
    Route::put('tags/{tag}', [TagController::class, 'update'])->name('tags.update');
    Route::delete('tags/{tag}', [TagController::class, 'destroy'])->name('tags.destroy');

    // ===== Vendas: produtos/estoque + orçamentos (sistema-vendas, OSPOS, CrmPlus) =====
    Route::get('products/search', [ProductController::class, 'search'])->name('products.search');
    Route::get('products/create', [ProductController::class, 'create'])->middleware('plan.limit:products')->name('products.create');
    Route::post('products', [ProductController::class, 'store'])->middleware('plan.limit:products')->name('products.store');
    Route::resource('products', ProductController::class)->except(['create', 'store']);
    Route::post('products/{product}/movement', [ProductController::class, 'movement'])->name('products.movement');

    Route::get('quotes/create', [QuoteController::class, 'create'])->middleware('plan.limit:quotes_per_month')->name('quotes.create');
    Route::post('quotes', [QuoteController::class, 'store'])->middleware('plan.limit:quotes_per_month')->name('quotes.store');
    Route::post('quotes/{quote}/duplicate', [QuoteController::class, 'duplicate'])->middleware('plan.limit:quotes_per_month')->name('quotes.duplicate');
    Route::resource('quotes', QuoteController::class)->except(['create', 'store']);
    Route::get('quotes/{quote}/print', [QuoteController::class, 'print'])->name('quotes.print');
    Route::post('quotes/{quote}/send', [QuoteController::class, 'send'])->name('quotes.send');
    Route::post('quotes/{quote}/approve', [QuoteController::class, 'approve'])->name('quotes.approve');
    Route::post('quotes/{quote}/reject', [QuoteController::class, 'reject'])->name('quotes.reject');

    // ===== Financeiro: contas a pagar/receber e fluxo de caixa =====
    Route::get('finance', [FinanceController::class, 'index'])->name('finance.index');
    Route::get('finance/entries', [FinanceController::class, 'entries'])->name('finance.entries');
    Route::post('finance/entries', [FinanceController::class, 'store'])->name('finance.store');
    Route::put('finance/entries/{entry}', [FinanceController::class, 'update'])->name('finance.update');
    Route::post('finance/entries/{entry}/settle', [FinanceController::class, 'settle'])->name('finance.settle');
    Route::post('finance/entries/{entry}/reopen', [FinanceController::class, 'reopen'])->name('finance.reopen');
    Route::post('finance/entries/{entry}/cancel', [FinanceController::class, 'cancel'])->name('finance.cancel');
    Route::delete('finance/entries/{entry}', [FinanceController::class, 'destroy'])->name('finance.destroy');
    Route::get('finance/categories', [FinanceController::class, 'categories'])->name('finance.categories');
    Route::post('finance/categories', [FinanceController::class, 'storeCategory'])->name('finance.categories.store');
    Route::delete('finance/categories/{category}', [FinanceController::class, 'destroyCategory'])->name('finance.categories.destroy');

    // ===== Captação de leads =====
    Route::get('lead-forms/create', [LeadFormController::class, 'create'])->middleware('plan.limit:lead_forms')->name('lead-forms.create');
    Route::post('lead-forms', [LeadFormController::class, 'store'])->middleware('plan.limit:lead_forms')->name('lead-forms.store');
    Route::resource('lead-forms', LeadFormController::class)->except(['create', 'store']);
    Route::post('lead-forms/{lead_form}/toggle', [LeadFormController::class, 'toggle'])->name('lead-forms.toggle');
});

// Formulário público de captação (sem login). Rate limit mais restrito no envio contra spam.
Route::get('/f/{slug}', [PublicLeadFormController::class, 'show'])->middleware('throttle:60,1')->name('lead-forms.public');
Route::post('/f/{slug}', [PublicLeadFormController::class, 'submit'])->middleware('throttle:6,1')->name('lead-forms.submit');

// Terms of Service (requires auth but not terms acceptance)
Route::middleware(['auth'])->group(function (): void {
    Route::get('/termos-de-uso', [TermsController::class, 'show'])->name('terms.show');
    Route::post('/termos-de-uso', [TermsController::class, 'accept'])->name('terms.accept');
});

require __DIR__.'/auth.php';

// Stripe Webhook (no CSRF, no auth)
Route::post('/stripe/webhook', [WebhookController::class, 'handleWebhook'])
    ->name('cashier.webhook');
