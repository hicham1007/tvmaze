<?php

declare(strict_types=1);

namespace App\Contracts;

use App\DTOs\Show;
use App\DTOs\ShowDetail;
use App\Exceptions\CatalogUnavailableException;

interface ShowCatalog
{
    /**
     * @return list<Show> Best match first.
     *
     * @throws CatalogUnavailableException
     */
    public function search(string $term): array;

    /**
     * @return ShowDetail|null Null when the catalog has no show with that id.
     *
     * @throws CatalogUnavailableException
     */
    public function find(int $id): ?ShowDetail;
}
