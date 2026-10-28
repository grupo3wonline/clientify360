<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/** Stripe could not be reached for billing data; callers render a recoverable error state. */
final class BillingUnavailableException extends RuntimeException {}
