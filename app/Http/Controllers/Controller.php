<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

abstract class Controller
{
    use AuthorizesRequests;

    /**
     * A query-string filter as a string. Malformed input such as `?search[]=x`
     * (an array) falls back to the default instead of breaking the query.
     */
    protected function queryString(Request $request, string $key, ?string $default = null): ?string
    {
        $value = $request->query($key);

        return is_scalar($value) ? (string) $value : $default;
    }
}
