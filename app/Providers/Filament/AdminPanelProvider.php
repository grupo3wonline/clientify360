<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Filament\Widgets\BusinessOverview;
use App\Http\Middleware\EnsureTermsAccepted;
use App\Http\Middleware\SetSpatieTeam;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\View\View;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('app')
            ->path('app')
            ->brandName('Clientify360')
            // No panel login page: guests are redirected to the Breeze /login,
            // the single implementation of the username + e-mail + password rule.
            // Brand colors from resources/brand/tokens.json (config/brand.php).
            ->colors([
                'primary' => (string) config('brand.colors.brand.600'),
                'gray' => Color::Slate,
                'success' => (string) config('brand.colors.accent.600'),
                'danger' => Color::Red,
                'warning' => Color::Amber,
                'info' => Color::Blue,
            ])
            ->brandLogo(fn (): string => asset('images/logo.svg'))
            ->darkModeBrandLogo(fn (): string => asset('images/logo-dark.svg'))
            ->brandLogoHeight('2rem')
            ->favicon(fn (): string => asset('images/favicon.svg'))
            // Shared theme script, panel styles, footer + CTA and the loading overlay.
            ->renderHook(PanelsRenderHook::HEAD_START, fn (): View => view('filament.hooks.theme-init'))
            ->renderHook(PanelsRenderHook::HEAD_END, fn (): View => view('filament.hooks.head-end'))
            ->renderHook(PanelsRenderHook::FOOTER, fn (): View => view('filament.hooks.footer'))
            ->renderHook(PanelsRenderHook::BODY_END, fn (): View => view('filament.hooks.body-end'))
            ->sidebarCollapsibleOnDesktop()
            ->navigationGroups([
                'Principal',
                'Comercial',
                'Financeiro',
                'Análise',
                'Conta',
            ])
            ->navigationItems([
                NavigationItem::make('Relatórios')
                    ->url(fn (): string => route('reports.index'))
                    ->icon('heroicon-o-chart-bar')
                    ->group('Análise')
                    ->sort(1)
                    ->visible(fn (): bool => auth()->user()?->can('reports.view') ?? false),
                NavigationItem::make('Notificações')
                    ->url(fn (): string => route('notifications.index'))
                    ->icon('heroicon-o-bell')
                    ->group('Análise')
                    ->sort(2),
                NavigationItem::make('IA / Análise')
                    ->url(fn (): string => route('ai.settings'))
                    ->icon('heroicon-o-sparkles')
                    ->group('Análise')
                    ->sort(3),
                NavigationItem::make('Equipe')
                    ->url(fn (): string => route('team.index'))
                    ->icon('heroicon-o-user-group')
                    ->group('Conta')
                    ->sort(1)
                    ->visible(fn (): bool => auth()->user()?->can('account.members') ?? false),
                // Financeiro: Blade overview + FinancialEntryResource (sort 1) + subscription pages.
                NavigationItem::make('Visão geral financeira')
                    ->url(fn (): string => route('finance.index'))
                    ->icon('heroicon-o-chart-pie')
                    ->group('Financeiro')
                    ->sort(0)
                    ->visible(fn (): bool => auth()->user()?->can('finance.view') ?? false),
                NavigationItem::make('Minha assinatura')
                    ->url(fn (): string => route('billing.index'))
                    ->icon('heroicon-o-credit-card')
                    ->group('Financeiro')
                    ->sort(2)
                    ->visible(fn (): bool => auth()->user()?->can('account.billing') ?? false),
                NavigationItem::make('Histórico de pagamentos')
                    ->url(fn (): string => route('billing.invoices'))
                    ->icon('heroicon-o-receipt-percent')
                    ->group('Financeiro')
                    ->sort(3)
                    ->visible(fn (): bool => auth()->user()?->can('account.billing') ?? false),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                BusinessOverview::class,
            ])
            ->darkMode(true)
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                // Define o contexto de equipe (conta) do Spatie dentro do painel.
                SetSpatieTeam::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                // Unverified users are redirected to /verify-email (not a 403).
                EnsureEmailIsVerified::class,
                EnsureTermsAccepted::class,
            ]);
    }
}
