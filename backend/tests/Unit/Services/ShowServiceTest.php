<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\DTOs\CastMember;
use App\DTOs\Show;
use App\DTOs\ShowDetail;
use App\Exceptions\ShowNotFoundException;
use App\Services\ShowService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tests\Support\InMemoryShowCatalog;

/**
 * Business logic tested on its own: no framework, no HTTP, no cache.
 */
class ShowServiceTest extends TestCase
{
    private InMemoryShowCatalog $catalog;

    private ShowService $service;

    protected function setUp(): void
    {
        $this->catalog = new InMemoryShowCatalog(shows: [
            new Show(id: 169, name: 'Breaking Bad', rating: 9.2),
            new Show(id: 510, name: 'Breaking In', rating: 7.4),
            new Show(id: 82, name: 'Game of Thrones', rating: 8.9),
        ]);
        $this->service = new ShowService($this->catalog);
    }

    public function test_search_keeps_the_order_of_the_catalog(): void
    {
        $shows = $this->service->search('breaking');

        $this->assertSame([169, 510], array_column($shows, 'id'));
    }

    #[DataProvider('equivalentTerms')]
    public function test_search_normalizes_the_term_before_searching(string $typed): void
    {
        $shows = $this->service->search($typed);

        $this->assertSame(['breaking bad'], $this->catalog->searchedTerms);
        $this->assertSame([169], array_column($shows, 'id'));
    }

    /**
     * @return array<string, array{string}>
     */
    public static function equivalentTerms(): array
    {
        return [
            'already normalized' => ['breaking bad'],
            'upper case' => ['BREAKING BAD'],
            'surrounding spaces' => ['  breaking bad  '],
            'repeated inner spaces' => ["breaking \t  bad"],
        ];
    }

    public function test_find_returns_the_show_with_that_id(): void
    {
        $catalog = new InMemoryShowCatalog(details: [
            new ShowDetail(id: 82, name: 'Game of Thrones'),
            $breakingBad = new ShowDetail(
                id: 169,
                name: 'Breaking Bad',
                genres: ['Drama', 'Crime'],
                rating: 9.2,
                summary: 'A chemistry teacher.',
                network: 'AMC',
                cast: [new CastMember(14245, 'Bryan Cranston', 'Walter White')],
            ),
        ]);

        $show = (new ShowService($catalog))->find(169);

        $this->assertEquals($breakingBad, $show);
    }

    public function test_find_fails_when_the_catalog_does_not_have_the_show(): void
    {
        $catalog = new InMemoryShowCatalog(details: [new ShowDetail(id: 82, name: 'Game of Thrones')]);

        try {
            (new ShowService($catalog))->find(999);
            $this->fail('Expected the show not to be found.');
        } catch (ShowNotFoundException $e) {
            $this->assertSame(999, $e->id);
            $this->assertSame('Show 999 was not found.', $e->getMessage());
        }
    }

    #[DataProvider('impossibleIds')]
    public function test_find_rejects_non_positive_ids(int $id): void
    {
        $catalog = new InMemoryShowCatalog;

        try {
            (new ShowService($catalog))->find($id);
            $this->fail('Expected the show not to be found.');
        } catch (ShowNotFoundException) {
            $this->assertSame([], $catalog->requestedIds);
        }
    }

    /**
     * @return array<string, array{int}>
     */
    public static function impossibleIds(): array
    {
        return ['zero' => [0], 'negative' => [-5]];
    }

    public function test_find_keeps_only_the_main_cast_in_billing_order(): void
    {
        $cast = array_map(
            fn (int $n) => new CastMember($n, "Actor {$n}", "Character {$n}"),
            range(1, 30),
        );
        $catalog = new InMemoryShowCatalog(details: [new ShowDetail(id: 1, name: 'Big Ensemble', rating: 7.0, cast: $cast)]);

        $show = (new ShowService($catalog))->find(1);

        $this->assertSame(range(1, 12), array_column($show->cast, 'personId'));
        // The rest of the show is untouched.
        $this->assertSame('Big Ensemble', $show->name);
        $this->assertSame(7.0, $show->rating);
    }
}
