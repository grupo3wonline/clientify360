<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreCookieConsentRequest;
use App\Services\CookieConsentService;
use Illuminate\Http\Response;

final class CookieConsentController extends Controller
{
    public function __construct(
        private readonly CookieConsentService $service
    ) {}

    /**
     * Store the visitor's cookie choice and return it as the c360_consent cookie.
     */
    public function store(StoreCookieConsentRequest $request): Response
    {
        $data = $request->validated();

        $consent = $this->service->record([
            'analytics' => (bool) $data['analytics'],
            'marketing' => (bool) $data['marketing'],
            'source' => (string) $data['source'],
            'consent_id' => $data['consent_id'] ?? null,
        ], $request->user(), $request);

        return response()->noContent()->withCookie($this->service->cookie($consent, $request));
    }
}
