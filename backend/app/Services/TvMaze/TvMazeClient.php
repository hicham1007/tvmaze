<?php

declare(strict_types=1);

namespace App\Services\TvMaze;

use App\Contracts\ShowCatalog;
use App\DTOs\ShowDetail;
use App\Exceptions\CatalogUnavailableException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Throwable;

final readonly class TvMazeClient implements ShowCatalog
{
    /**
     * @param  int  $attempts  Total tries per request (1 = no retry).
     */
    public function __construct(
        private string $baseUrl,
        private string $userAgent,
        private int $connectTimeout,
        private int $timeout,
        private int $attempts,
        private int $retrySleepMs,
    ) {}

    public function search(string $term): array
    {
        $results = $this->json($this->get('/search/shows', ['q' => $term]));

        return array_map(
            TvMazeMapper::toShow(...),
            // One broken result should not take the whole search down.
            array_values(array_filter(array_column($results, 'show'), TvMazeMapper::isShow(...))),
        );
    }

    public function find(int $id): ?ShowDetail
    {
        $response = $this->get("/shows/{$id}", ['embed' => 'cast']);

        if ($response->notFound()) {
            return null;
        }

        $show = $this->json($response);

        if (! TvMazeMapper::isShow($show)) {
            throw CatalogUnavailableException::invalidResponse($response->status());
        }

        return TvMazeMapper::toShowDetail($show);
    }

    /**
     * @param  array<string, scalar>  $query
     *
     * @throws CatalogUnavailableException
     */
    private function get(string $path, array $query): Response
    {
        try {
            return Http::baseUrl($this->baseUrl)
                ->withUserAgent($this->userAgent)
                ->acceptJson()
                ->connectTimeout($this->connectTimeout)
                ->timeout($this->timeout)
                ->retry(
                    max(1, $this->attempts),
                    $this->retrySleepMs,
                    // Only retry what may succeed the second time: network errors and 5xx.
                    fn (Throwable $e) => $e instanceof ConnectionException
                        || ($e instanceof RequestException && $e->response->serverError()),
                    throw: false,
                )
                ->get($path, $query);
        } catch (ConnectionException $e) {
            throw CatalogUnavailableException::unreachable($e);
        }
    }

    /**
     * @return array<mixed>
     *
     * @throws CatalogUnavailableException
     */
    private function json(Response $response): array
    {
        if ($response->tooManyRequests()) {
            throw CatalogUnavailableException::rateLimited();
        }

        $body = $response->json();

        if ($response->failed() || ! is_array($body)) {
            throw CatalogUnavailableException::invalidResponse($response->status());
        }

        return $body;
    }
}
