<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Contracts\ShowCatalog;
use App\DTOs\Show;
use App\DTOs\ShowDetail;
use App\Exceptions\CatalogUnavailableException;

final class InMemoryShowCatalog implements ShowCatalog
{
    /** @var list<string> */
    public array $searchedTerms = [];

    /** @var list<int> */
    public array $requestedIds = [];

    private ?CatalogUnavailableException $failure = null;

    /**
     * @param  list<Show>  $shows
     * @param  list<ShowDetail>  $details
     */
    public function __construct(private array $shows = [], private array $details = []) {}

    public function failWith(CatalogUnavailableException $failure): void
    {
        $this->failure = $failure;
    }

    private function throwIfFailing(): void
    {
        if ($this->failure !== null) {
            throw $this->failure;
        }
    }

    public function search(string $term): array
    {
        $this->searchedTerms[] = $term;
        $this->throwIfFailing();

        return array_values(array_filter(
            $this->shows,
            fn (Show $show) => str_contains(mb_strtolower($show->name), $term),
        ));
    }

    public function find(int $id): ?ShowDetail
    {
        $this->requestedIds[] = $id;
        $this->throwIfFailing();

        foreach ($this->details as $detail) {
            if ($detail->id === $id) {
                return $detail;
            }
        }

        return null;
    }
}
