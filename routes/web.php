<?php

declare(strict_types=1);

use App\Http\Controllers\AccountLogoController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AiController;
use App\Http\Controllers\ApiTokenController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\ClientAttachmentController;
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
use App\Http\Middleware\EnsureStripeWebhookIsConfigured;
use Illuminate\Support\Facades\Route;
use Laravel\Cashier\Http\Controllers\WebhookController;

// Public page (Landing / Hotsite — página única de vendas)
Route::middleware('throttle:public')->group(function (): void {
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
    ->middleware(['auth', 'throttle:app'])->name('dashboard');

// Authenticated routes
Route::middleware(['auth', 'verified', 'terms.accepted', 'throttle:app'])->group(function (): void {
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/profile/exportar-dados', [ProfileController::class, 'exportPersonalData'])->name('profile.export');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Tokens de API: só a criação depende do plano; revogar funciona em qualquer plano.
    Route::post('/profile/api-tokens', [ApiTokenController::class, 'store'])->middleware('plan.feature:api')->name('profile.tokens.store');
    Route::delete('/profile/api-tokens/{tokenId}', [ApiTokenController::class, 'destroy'])->name('profile.tokens.destroy');

    // Logotipo da conta (usado nos PDFs)
    Route::get('/profile/account-logo', [AccountLogoController::class, 'show'])->name('account.logo.show');
    Route::middleware('permission:account.settings')->group(function (): void {
        Route::post('/profile/account-logo', [AccountLogoController::class, 'update'])->name('account.logo.update');
        Route::delete('/profile/account-logo', [AccountLogoController::class, 'destroy'])->name('account.logo.destroy');
    });

    // Equipe / Colaboradores (ACL: permissão account.members)
    Route::middleware('permission:account.members')->group(function (): void {
        Route::get('/equipe', [TeamController::class, 'index'])->name('team.index');
        Route::post('/equipe/convites', [TeamController::class, 'invite'])->name('team.invite');
        Route::patch('/equipe/membros/{member}/papel', [TeamController::class, 'updateRole'])->name('team.updateRole');
        Route::delete('/equipe/membros/{member}', [TeamController::class, 'removeMember'])->name('team.removeMember');
        Route::delete('/equipe/convites/{invitation}', [TeamController::class, 'revokeInvite'])->name('team.revokeInvite');
    });

    // Billing / Subscription (apenas quem tem permissão de cobrança — Dono)
    Route::middleware('permission:account.billing')->group(function (): void {
        Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
        Route::get('/billing/invoices', [BillingController::class, 'invoices'])->name('billing.invoices');
        Route::post('/billing/subscribe', [BillingController::class, 'subscribe'])->name('billing.subscribe');
        Route::post('/billing/cancel', [BillingController::class, 'cancel'])->name('billing.cancel');
        Route::post('/billing/resume', [BillingController::class, 'resume'])->name('billing.resume');
        Route::get('/billing/portal', [BillingController::class, 'portal'])->name('billing.portal');
    });

    // Clients CRUD + Agenda + Atendimentos + Tags (ACL: clients.manage escreve, clients.view lê)
    // create/store ANTES do resource: senão "clients/{client}" captura "clients/create" (404)
    Route::middleware('permission:clients.manage')->group(function (): void {
        Route::get('clients/create', [ClientController::class, 'create'])->middleware('plan.limit')->name('clients.create');
        Route::post('clients', [ClientController::class, 'store'])->middleware('plan.limit')->name('clients.store');
        Route::resource('clients', ClientController::class)->only(['edit', 'update', 'destroy']);
        Route::post('clients/{client}/attachments', [ClientAttachmentController::class, 'store'])->middleware('plan.limit:client_attachments')->name('clients.attachments.store');
        Route::delete('clients/{client}/attachments/{media}', [ClientAttachmentController::class, 'destroy'])->name('clients.attachments.destroy');

        Route::post('reminders', [ReminderController::class, 'store'])->name('reminders.store');
        Route::patch('reminders/{reminder}/complete', [ReminderController::class, 'complete'])->name('reminders.complete');
        Route::delete('reminders/{reminder}', [ReminderController::class, 'destroy'])->name('reminders.destroy');

        Route::post('interactions', [InteractionController::class, 'store'])->name('interactions.store');
        Route::patch('interactions/{interaction}/status', [InteractionController::class, 'updateStatus'])->name('interactions.updateStatus');
        Route::delete('interactions/{interaction}', [InteractionController::class, 'destroy'])->name('interactions.destroy');

        Route::post('tags', [TagController::class, 'store'])->name('tags.store');
        Route::put('tags/{tag}', [TagController::class, 'update'])->name('tags.update');
        Route::delete('tags/{tag}', [TagController::class, 'destroy'])->name('tags.destroy');
    });
    Route::middleware('permission:clients.view')->group(function (): void {
        Route::resource('clients', ClientController::class)->only(['index', 'show']);
        Route::get('clients/{client}/attachments/{media}', [ClientAttachmentController::class, 'show'])->name('clients.attachments.show');
        Route::get('reminders', [ReminderController::class, 'index'])->name('reminders.index');
        Route::get('interactions', [InteractionController::class, 'index'])->name('interactions.index');
        Route::get('tags', [TagController::class, 'index'])->name('tags.index');
    });

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
        Route::get('service-orders/{service_order}/pdf', [ServiceOrderController::class, 'pdf'])->middleware('plan.feature:pdf')->name('service-orders.pdf');
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

    // Reports
    Route::get('reports', [ReportController::class, 'index'])->middleware('permission:reports.view')->name('reports.index');

    // Notifications (pessoais: cada usuário vê e dispensa as suas)
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/mark-read/{id}', [NotificationController::class, 'markAsRead'])->name('notifications.markRead');
    Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.markAllRead');

    Route::middleware('permission:clients.view')->group(function (): void {
        // AI Analysis (Professional plan only)
        Route::get('ai/settings', [AiController::class, 'settings'])->name('ai.settings');
        Route::post('ai/settings', [AiController::class, 'saveSettings'])->name('ai.saveSettings');
        Route::post('ai/analyze/{client}', [AiController::class, 'analyze'])->name('ai.analyze');

        // Export (Professional plan only)
        Route::middleware('plan.feature:export')->group(function (): void {
            Route::get('/export/clients', [ExportController::class, 'clients'])->name('export.clients');
            // Queues a job and sends an e-mail: a state change, so POST + CSRF.
            Route::post('/export/clients/xlsx', [ExportController::class, 'clientsExcel'])->middleware('throttle:3,1')->name('export.clients.xlsx');
        });

        // Signed link sent by e-mail when the queued XLSX is ready.
        Route::get('/export/downloads', [ExportController::class, 'download'])->middleware('signed')->name('export.download');
    });

    // ===== CRM comercial (CrmPlus + crm-master) =====
    Route::middleware('permission:deals.manage')->group(function (): void {
        Route::post('deals', [DealController::class, 'store'])->name('deals.store');
        Route::put('deals/{deal}', [DealController::class, 'update'])->name('deals.update');
        Route::delete('deals/{deal}', [DealController::class, 'destroy'])->name('deals.destroy');
        Route::patch('deals/{deal}/move', [DealController::class, 'move'])->name('deals.move');
        Route::post('deals/{deal}/win', [DealController::class, 'win'])->name('deals.win');
        Route::post('deals/{deal}/lose', [DealController::class, 'lose'])->name('deals.lose');
        Route::post('deals/{deal}/reopen', [DealController::class, 'reopen'])->name('deals.reopen');

        Route::post('pipeline/stages', [PipelineStageController::class, 'store'])->name('stages.store');
        Route::put('pipeline/stages/{stage}', [PipelineStageController::class, 'update'])->name('stages.update');
        Route::delete('pipeline/stages/{stage}', [PipelineStageController::class, 'destroy'])->name('stages.destroy');

        Route::post('activities', [ActivityController::class, 'store'])->name('activities.store');
        Route::delete('activities/{activity}', [ActivityController::class, 'destroy'])->name('activities.destroy');
    });
    Route::middleware('permission:deals.view')->group(function (): void {
        Route::get('pipeline', [DealController::class, 'index'])->name('deals.index');
        Route::get('pipeline/stages', [PipelineStageController::class, 'index'])->name('stages.index');
        Route::get('deals', [DealController::class, 'list'])->name('deals.list');
        Route::get('deals/{deal}', [DealController::class, 'show'])->name('deals.show');
        Route::get('activities', [ActivityController::class, 'index'])->name('activities.index');
    });

    // ===== Vendas: produtos/estoque + orçamentos (sistema-vendas, OSPOS, CrmPlus) =====
    // create/store ANTES do resource: senão "products/{product}" captura "products/create".
    Route::middleware('permission:products.manage')->group(function (): void {
        Route::get('products/create', [ProductController::class, 'create'])->middleware('plan.limit:products')->name('products.create');
        Route::post('products', [ProductController::class, 'store'])->middleware('plan.limit:products')->name('products.store');
        Route::resource('products', ProductController::class)->only(['edit', 'update', 'destroy']);
        Route::post('products/{product}/movement', [ProductController::class, 'movement'])->name('products.movement');
    });
    Route::middleware('permission:products.view')->group(function (): void {
        Route::get('products/search', [ProductController::class, 'search'])->name('products.search');
        Route::resource('products', ProductController::class)->only(['index', 'show']);
    });

    Route::middleware('permission:quotes.manage')->group(function (): void {
        Route::get('quotes/create', [QuoteController::class, 'create'])->middleware('plan.limit:quotes_per_month')->name('quotes.create');
        Route::post('quotes', [QuoteController::class, 'store'])->middleware('plan.limit:quotes_per_month')->name('quotes.store');
        Route::post('quotes/{quote}/duplicate', [QuoteController::class, 'duplicate'])->middleware('plan.limit:quotes_per_month')->name('quotes.duplicate');
        Route::resource('quotes', QuoteController::class)->only(['edit', 'update', 'destroy']);
        Route::post('quotes/{quote}/send', [QuoteController::class, 'send'])->name('quotes.send');
        Route::post('quotes/{quote}/approve', [QuoteController::class, 'approve'])->name('quotes.approve');
        Route::post('quotes/{quote}/reject', [QuoteController::class, 'reject'])->name('quotes.reject');
    });
    Route::middleware('permission:quotes.view')->group(function (): void {
        Route::resource('quotes', QuoteController::class)->only(['index', 'show']);
        Route::get('quotes/{quote}/print', [QuoteController::class, 'print'])->name('quotes.print');
        Route::get('quotes/{quote}/pdf', [QuoteController::class, 'pdf'])->middleware('plan.feature:pdf')->name('quotes.pdf');
    });

    // ===== Financeiro: contas a pagar/receber e fluxo de caixa =====
    Route::middleware('permission:finance.manage')->group(function (): void {
        Route::post('finance/entries', [FinanceController::class, 'store'])->name('finance.store');
        Route::put('finance/entries/{entry}', [FinanceController::class, 'update'])->name('finance.update');
        Route::post('finance/entries/{entry}/settle', [FinanceController::class, 'settle'])->name('finance.settle');
        Route::post('finance/entries/{entry}/reopen', [FinanceController::class, 'reopen'])->name('finance.reopen');
        Route::post('finance/entries/{entry}/cancel', [FinanceController::class, 'cancel'])->name('finance.cancel');
        Route::delete('finance/entries/{entry}', [FinanceController::class, 'destroy'])->name('finance.destroy');
        Route::post('finance/categories', [FinanceController::class, 'storeCategory'])->name('finance.categories.store');
        Route::delete('finance/categories/{category}', [FinanceController::class, 'destroyCategory'])->name('finance.categories.destroy');
    });
    Route::middleware('permission:finance.view')->group(function (): void {
        Route::get('finance', [FinanceController::class, 'index'])->name('finance.index');
        Route::get('finance/entries', [FinanceController::class, 'entries'])->name('finance.entries');
        Route::get('finance/categories', [FinanceController::class, 'categories'])->name('finance.categories');
    });

    // ===== Captação de leads =====
    Route::middleware('permission:leads.manage')->group(function (): void {
        Route::get('lead-forms/create', [LeadFormController::class, 'create'])->middleware('plan.limit:lead_forms')->name('lead-forms.create');
        Route::post('lead-forms', [LeadFormController::class, 'store'])->middleware('plan.limit:lead_forms')->name('lead-forms.store');
        Route::resource('lead-forms', LeadFormController::class)->only(['edit', 'update', 'destroy']);
        Route::post('lead-forms/{lead_form}/toggle', [LeadFormController::class, 'toggle'])->name('lead-forms.toggle');
    });
    Route::middleware('permission:leads.view')->group(function (): void {
        Route::resource('lead-forms', LeadFormController::class)->only(['index', 'show']);
    });
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

// Stripe Webhook (no CSRF, no auth). Cashier verifies the signature whenever the
// secret is set; the guard rejects (503) in production when it is missing.
Route::post('/stripe/webhook', [WebhookController::class, 'handleWebhook'])
    ->middleware(EnsureStripeWebhookIsConfigured::class)
    ->name('cashier.webhook');
