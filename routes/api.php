<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\ClientController;
use App\Http\Controllers\Api\V1\MeController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\QuoteController;
use App\Http\Middleware\SetSpatieTeam;
use Illuminate\Support\Facades\Route;

// Versioned REST API (token-only, account-isolated, Profissional plan).
Route::prefix('v1')
    ->name('api.v1.')
    ->middleware(['auth:sanctum', SetSpatieTeam::class, 'plan.feature:api', 'throttle:api'])
    ->group(function (): void {
        Route::get('me', MeController::class)->name('me');

        Route::middleware(['abilities:clients:read', 'permission:clients.view'])->group(function (): void {
            Route::get('clients', [ClientController::class, 'index'])->name('clients.index');
            Route::get('clients/{client}', [ClientController::class, 'show'])->name('clients.show');
        });
        Route::middleware(['abilities:clients:write', 'permission:clients.manage'])->group(function (): void {
            Route::post('clients', [ClientController::class, 'store'])->name('clients.store');
            Route::put('clients/{client}', [ClientController::class, 'update'])->name('clients.update');
            Route::delete('clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');
        });

        Route::middleware(['abilities:products:read', 'permission:products.view'])->group(function (): void {
            Route::get('products', [ProductController::class, 'index'])->name('products.index');
            Route::get('products/{product}', [ProductController::class, 'show'])->name('products.show');
        });

        Route::middleware(['abilities:quotes:read', 'permission:quotes.view'])->group(function (): void {
            Route::get('quotes', [QuoteController::class, 'index'])->name('quotes.index');
            Route::get('quotes/{quote}', [QuoteController::class, 'show'])->name('quotes.show');
        });
    });
