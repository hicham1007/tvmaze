<?php

declare(strict_types=1);

use App\Enums\CatalogFailure;
use App\Exceptions\CatalogUnavailableException;
use App\Exceptions\ShowNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        health: '/up',
    )
    ->withMiddleware()
    ->withExceptions(function (Exceptions $exceptions): void {
        // Every response of this service is JSON, including framework errors (404, 422, 500...).
        $exceptions->shouldRenderJsonWhen(fn (Request $request) => true);

        $exceptions->dontReport(ShowNotFoundException::class);

        $error = fn (Throwable $e, int $status, string $code) => response()->json(
            ['message' => $e->getMessage(), 'code' => $code],
            $status,
        );

        $exceptions->render(fn (ShowNotFoundException $e) => $error($e, 404, 'show_not_found'));
        $exceptions->render(fn (CatalogUnavailableException $e) => match ($e->failure) {
            CatalogFailure::Unreachable => $error($e, 504, 'upstream_unreachable'),
            CatalogFailure::RateLimited => $error($e, 503, 'upstream_rate_limited'),
            CatalogFailure::InvalidResponse => $error($e, 502, 'upstream_error'),
        });
    })->create();
