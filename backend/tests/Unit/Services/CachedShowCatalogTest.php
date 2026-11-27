<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\DTOs\Show;
use App\DTOs\ShowDetail;
use App\Exceptions\CatalogUnavailableException;
use App\Services\CachedShowCatalog;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;
use PHPUnit\Framework\TestCase;
use Tests\Support\InMemoryShowCatalog;

class CachedShowCatalogTest extends TestCase
{
    private Repository $cache;

    protected function setUp(): void
    {
        $this->cache = new Repository(new ArrayStore);
    }

    private function cached(InMemoryShowCatalog $inner, string $provider = 'tvmaze'): CachedShowCatalog
    {
        return new CachedShowCatalog($inner, $this->cache, $provider, searchTtl: 600, showTtl: 3600);
    }

    public function test_it_asks_the_inner_catalog_only_once_for_the_same_question(): void
    {
        $inner = new InMemoryShowCatalog(
            shows: [new Show(id: 1, name: 'Dark')],
            details: [new ShowDetail(id: 1, name: 'Dark')],
        );
        $catalog = $this->cached($inner);

        $this->assertEquals($catalog->search('dark'), $catalog->search('dark'));
        $this->assertEquals($catalog->find(1), $catalog->find(1));

        $this->assertSame(['dark'], $inner->searchedTerms);
        $this->assertSame([1], $inner->requestedIds);
    }

    public function test_a_show_that_was_not_found_is_asked_for_again(): void
    {
        $inner = new InMemoryShowCatalog;
        $catalog = $this->cached($inner);

        $this->assertNull($catalog->find(404));
        $this->assertNull($catalog->find(404));

        $this->assertSame([404, 404], $inner->requestedIds);
        $this->assertFalse($this->cache->has('shows:tvmaze:detail:404'));
    }

    public function test_failures_are_not_remembered(): void
    {
        $inner = new InMemoryShowCatalog(shows: [new Show(id: 1, name: 'Dark')]);
        $catalog = $this->cached($inner);
        $inner->failWith(CatalogUnavailableException::unreachable());

        try {
            $catalog->search('dark');
            $this->fail('Expected the failure of the inner catalog.');
        } catch (CatalogUnavailableException) {
            // The provider recovers: the next search must reach it and succeed.
            $recovered = $this->cached(new InMemoryShowCatalog(shows: [new Show(id: 1, name: 'Dark')]));

            $this->assertCount(1, $recovered->search('dark'));
        }
    }

    public function test_answers_of_one_provider_are_never_served_for_another(): void
    {
        // The same id is a different show in each provider.
        $tvmaze = new InMemoryShowCatalog(
            shows: [new Show(id: 1, name: 'Dark')],
            details: [new ShowDetail(id: 1, name: 'Dark')],
        );
        $other = new InMemoryShowCatalog(
            shows: [new Show(id: 1, name: 'Dark Matter')],
            details: [new ShowDetail(id: 1, name: 'Dark Matter')],
        );

        $this->cached($tvmaze, 'tvmaze')->find(1);
        $this->cached($tvmaze, 'tvmaze')->search('dark');

        $this->assertSame('Dark Matter', $this->cached($other, 'other')->find(1)?->name);
        $this->assertSame(['Dark Matter'], array_column($this->cached($other, 'other')->search('dark'), 'name'));
    }
}
