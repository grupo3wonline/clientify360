<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Password lifetime
    |--------------------------------------------------------------------------
    |
    | Passwords expire after "max_age_days" (counted from users.password_changed_at).
    | From "warning_days" before the deadline the user sees a reminder; after it,
    | every authenticated area asks for a new password before continuing.
    |
    */

    'password' => [
        'max_age_days' => (int) env('PASSWORD_MAX_AGE_DAYS', 90),
        'warning_days' => (int) env('PASSWORD_WARNING_DAYS', 7),
    ],

    /*
    |--------------------------------------------------------------------------
    | Subscription renewal reminder
    |--------------------------------------------------------------------------
    |
    | Days before the renewal (or the end of a canceled subscription) from which
    | the account owner sees a reminder.
    |
    */

    'subscription_warning_days' => (int) env('SUBSCRIPTION_WARNING_DAYS', 7),

];
