<?php

declare(strict_types=1);

use Illuminate\Console\Scheduling\Schedule;

it('schedules the queue worker, the prunes and the export purge', function (): void {
    $events = collect(app(Schedule::class)->events());
    $commands = $events->map(fn ($event): string => (string) ($event->command ?? $event->description ?? ''));

    $worker = $events->first(fn ($event): bool => str_contains((string) $event->command, 'queue:work --stop-when-empty --max-time=50 --tries=3'));

    expect($worker)->not->toBeNull()
        ->and($worker->expression)->toBe('* * * * *')
        ->and($worker->withoutOverlapping)->toBeTrue()
        ->and($worker->runInBackground)->toBeTrue()
        ->and($commands->contains(fn (string $c): bool => str_contains($c, 'sanctum:prune-expired --hours=24')))->toBeTrue()
        ->and($commands->contains(fn (string $c): bool => str_contains($c, 'model:prune')))->toBeTrue()
        ->and($commands->contains('purge-old-exports'))->toBeTrue();
});
