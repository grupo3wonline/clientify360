<?php

declare(strict_types=1);

use App\Http\Middleware\SetSpatieTeam;
use Illuminate\Support\Facades\Route;

// Versioned REST API (token-only, account-isolated). Routes are added in Phase 3.
Route::prefix('v1')
    ->name('api.v1.')
    ->middleware(['auth:sanctum', SetSpatieTeam::class, 'plan.feature:api', 'throttle:api'])
    ->group(function (): void {});
