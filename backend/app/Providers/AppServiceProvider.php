<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\ShowCatalog;
use App\Services\CachedShowCatalog;
use App\Services\TvMaze\TvMazeClient;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ShowCatalog::class, function (Application $app): ShowCatalog {
            $provider = (string) config('services.shows.provider');

            return new CachedShowCatalog(
                $this->catalogOf($provider),
                $app->make(CacheRepository::class),
                $provider,
                searchTtl: (int) config('services.shows.cache_ttl.search'),
                showTtl: (int) config('services.shows.cache_ttl.show'),
            );
        });
    }

    private function catalogOf(string $provider): ShowCatalog
    {
        return match ($provider) {
            'tvmaze' => new TvMazeClient(
                baseUrl: (string) config('services.tvmaze.base_url'),
                userAgent: (string) config('services.tvmaze.user_agent'),
                connectTimeout: (int) config('services.tvmaze.connect_timeout'),
                timeout: (int) config('services.tvmaze.timeout'),
                attempts: (int) config('services.tvmaze.attempts'),
                retrySleepMs: (int) config('services.tvmaze.retry_sleep_ms'),
            ),
            default => throw new InvalidArgumentException(
                "Unknown shows provider \"{$provider}\". Check SHOWS_PROVIDER; available: tvmaze."
            ),
        };
    }
}
