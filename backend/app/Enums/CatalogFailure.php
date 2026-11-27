<?php

declare(strict_types=1);

namespace App\Enums;

enum CatalogFailure: string
{
    case Unreachable = 'unreachable';

    case RateLimited = 'rate_limited';

    case InvalidResponse = 'invalid_response';
}
