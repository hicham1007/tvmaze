<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Enums\CatalogFailure;
use RuntimeException;
use Throwable;

final class CatalogUnavailableException extends RuntimeException
{
    public function __construct(public readonly CatalogFailure $failure, string $message, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }

    public static function unreachable(?Throwable $previous = null): self
    {
        return new self(CatalogFailure::Unreachable, 'The TV shows provider did not respond in time.', $previous);
    }

    public static function rateLimited(): self
    {
        return new self(CatalogFailure::RateLimited, 'The TV shows provider is rate limiting requests. Try again in a few seconds.');
    }

    public static function invalidResponse(int $status): self
    {
        return new self(CatalogFailure::InvalidResponse, "The TV shows provider returned an unexpected response ({$status}).");
    }
}
