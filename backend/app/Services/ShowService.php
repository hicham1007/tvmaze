<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ShowCatalog;
use App\DTOs\Show;
use App\DTOs\ShowDetail;
use App\Exceptions\CatalogUnavailableException;
use App\Exceptions\ShowNotFoundException;

class ShowService
{
    /** The detail lists the main cast only, not every guest appearance. */
    public const MAIN_CAST_SIZE = 12;

    public function __construct(private readonly ShowCatalog $catalog) {}

    /**
     * @return list<Show> Best match first.
     *
     * @throws CatalogUnavailableException
     */
    public function search(string $term): array
    {
        // "Breaking   BAD" and "breaking bad" are the same search (and the same cache entry).
        $term = mb_strtolower(trim(preg_replace('/\s+/u', ' ', $term) ?? $term));

        return $this->catalog->search($term);
    }

    /**
     * @throws ShowNotFoundException
     * @throws CatalogUnavailableException
     */
    public function find(int $id): ShowDetail
    {
        $show = $id > 0 ? $this->catalog->find($id) : null;

        if ($show === null) {
            throw new ShowNotFoundException($id);
        }

        return $show->withMainCast(self::MAIN_CAST_SIZE);
    }
}
