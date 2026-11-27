<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * The other feature tests use the in-memory cache store, which keeps objects
 * as they are. Production uses the file store, which serializes them: these
 * tests make sure a cached answer survives that round trip.
 */
class PersistentCacheTest extends TestCase
{
    private string $cachePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cachePath = sys_get_temp_dir().'/shows-cache-test-'.uniqid();
        config(['cache.default' => 'file', 'cache.stores.file.path' => $this->cachePath]);

        Http::preventStrayRequests();
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->cachePath);

        parent::tearDown();
    }

    public function test_a_cached_search_survives_serialization(): void
    {
        Http::fake(['api.tvmaze.com/*' => Http::response([
            ['show' => ['id' => 169, 'name' => 'Breaking Bad', 'genres' => ['Drama'], 'rating' => ['average' => 9.2]]],
        ])]);

        $first = $this->getJson('/api/shows?q=breaking')->assertOk()->json();
        $second = $this->getJson('/api/shows?q=breaking')->assertOk()->json();

        $this->assertSame($first, $second);
        $this->assertSame('Breaking Bad', $second['data'][0]['name']);
        Http::assertSentCount(1);
    }

    public function test_a_cached_detail_survives_serialization(): void
    {
        Http::fake(['api.tvmaze.com/*' => Http::response([
            'id' => 169,
            'name' => 'Breaking Bad',
            '_embedded' => ['cast' => [
                ['person' => ['id' => 1, 'name' => 'Bryan Cranston'], 'character' => ['name' => 'Walter White']],
            ]],
        ])]);

        $first = $this->getJson('/api/shows/169')->assertOk()->json();
        $second = $this->getJson('/api/shows/169')->assertOk()->json();

        $this->assertSame($first, $second);
        $this->assertSame('Bryan Cranston', $second['data']['cast'][0]['name']);
        Http::assertSentCount(1);
    }
}
