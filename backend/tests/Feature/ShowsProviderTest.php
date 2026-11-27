<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Contracts\ShowCatalog;
use App\DTOs\Show;
use App\DTOs\ShowDetail;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use RuntimeException;
use Tests\Support\InMemoryShowCatalog;
use Tests\TestCase;

class ShowsProviderTest extends TestCase
{
    public function test_an_unknown_provider_is_rejected(): void
    {
        config(['services.shows.provider' => 'imdb']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown shows provider "imdb". Check SHOWS_PROVIDER; available: tvmaze.');

        $this->app->make(ShowCatalog::class);
    }

    public function test_the_api_works_the_same_with_another_catalog(): void
    {
        Http::preventStrayRequests();

        // Nothing but the catalog changes: same routes, same JSON, same errors.
        $this->app->instance(ShowCatalog::class, new InMemoryShowCatalog(
            shows: [new Show(id: 7, name: 'From Another Provider', rating: 8.0)],
            details: [new ShowDetail(id: 7, name: 'From Another Provider')],
        ));

        $this->getJson('/api/shows?q=another')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'From Another Provider');
        $this->getJson('/api/shows/7')->assertOk()->assertJsonPath('data.id', 7);
        $this->getJson('/api/shows/8')->assertNotFound()->assertJsonPath('code', 'show_not_found');

        Http::assertNothingSent();
    }

    public function test_an_unexpected_failure_is_a_500_in_json_that_leaks_nothing(): void
    {
        config(['app.debug' => false]);

        $this->app->bind(ShowCatalog::class, fn () => new class implements ShowCatalog
        {
            public function search(string $term): array
            {
                throw new RuntimeException('Secret detail: db password is hunter2');
            }

            public function find(int $id): ?ShowDetail
            {
                throw new RuntimeException('Secret detail: db password is hunter2');
            }
        });

        foreach (['/api/shows?q=dark', '/api/shows/1'] as $url) {
            $this->get($url)
                ->assertStatus(500)
                ->assertHeader('content-type', 'application/json')
                ->assertExactJson(['message' => 'Server Error']);
        }
    }
}
