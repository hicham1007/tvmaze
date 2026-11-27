<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ShowCatalog;
use App\DTOs\ShowDetail;
use Illuminate\Contracts\Cache\Repository;

/**
 * Keys carry the name of the provider: ids and results of one provider mean
 * nothing for another, so switching must never serve the old one's answers.
 */
final readonly class CachedShowCatalog implements ShowCatalog
{
    public function __construct(
        private ShowCatalog $inner,
        private Repository $cache,
        private string $provider,
        private int $searchTtl,
        private int $showTtl,
    ) {}

    public function search(string $term): array
    {
        return $this->cache->remember("shows:{$this->provider}:search:".sha1($term), $this->searchTtl, fn () => $this->inner->search($term));
    }

    public function find(int $id): ?ShowDetail
    {
        $key = "shows:{$this->provider}:detail:{$id}";

        $show = $this->cache->get($key);

        if (! $show instanceof ShowDetail) {
            $show = $this->inner->find($id);

            // A show that does not exist today may exist tomorrow: only real answers are stored.
            if ($show !== null) {
                $this->cache->put($key, $show, $this->showTtl);
            }
        }

        return $show;
    }
}
