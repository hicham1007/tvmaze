<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ShowDetailTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
    }

    public function test_it_returns_the_full_detail_of_a_show(): void
    {
        Http::fake([
            'api.tvmaze.com/shows/82?embed=cast' => Http::response([
                'id' => 82,
                'name' => 'Game of Thrones',
                'type' => 'Scripted',
                'language' => 'English',
                'genres' => ['Drama', 'Adventure', 'Fantasy'],
                'status' => 'Ended',
                'runtime' => 60,
                'averageRuntime' => 61,
                'premiered' => '2011-04-17',
                'ended' => '2019-05-19',
                'officialSite' => 'http://www.hbo.com/game-of-thrones',
                'schedule' => ['time' => '21:00', 'days' => ['Sunday']],
                'rating' => ['average' => 8.9],
                'network' => ['id' => 8, 'name' => 'HBO', 'country' => ['name' => 'United States', 'code' => 'US']],
                'webChannel' => null,
                'image' => ['medium' => 'https://img.test/82-m.jpg', 'original' => 'https://img.test/82-o.jpg'],
                'summary' => '<p>Based on the <b>bestselling</b> books by George R.R. Martin &amp; co.</p><p>Second paragraph.</p>',
                '_embedded' => ['cast' => [
                    [
                        'person' => ['id' => 14075, 'name' => 'Kit Harington', 'image' => ['medium' => 'https://img.test/kit.jpg']],
                        'character' => ['id' => 4, 'name' => 'Jon Snow', 'image' => null],
                    ],
                    [
                        'person' => ['id' => 14076, 'name' => 'Emilia Clarke', 'image' => null],
                        'character' => ['id' => 5, 'name' => 'Daenerys Targaryen', 'image' => null],
                    ],
                ]],
            ]),
        ]);

        $this->getJson('/api/shows/82')
            ->assertOk()
            ->assertExactJson(['data' => [
                'id' => 82,
                'name' => 'Game of Thrones',
                'image' => 'https://img.test/82-o.jpg',
                'genres' => ['Drama', 'Adventure', 'Fantasy'],
                'rating' => 8.9,
                'premiered' => '2011-04-17',
                'summary' => "Based on the bestselling books by George R.R. Martin & co.\n\nSecond paragraph.",
                'type' => 'Scripted',
                'language' => 'English',
                'status' => 'Ended',
                'ended' => '2019-05-19',
                'runtime' => 60,
                'network' => 'HBO',
                'country' => 'United States',
                'schedule' => ['time' => '21:00', 'days' => ['Sunday']],
                'officialSite' => 'http://www.hbo.com/game-of-thrones',
                'cast' => [
                    ['id' => 14075, 'name' => 'Kit Harington', 'character' => 'Jon Snow', 'image' => 'https://img.test/kit.jpg'],
                    ['id' => 14076, 'name' => 'Emilia Clarke', 'character' => 'Daenerys Targaryen', 'image' => null],
                ],
            ]]);
    }

    public function test_it_answers_404_when_the_show_does_not_exist(): void
    {
        Http::fake(['api.tvmaze.com/*' => Http::response(['name' => 'Not Found', 'status' => 404], 404)]);

        $this->getJson('/api/shows/999999')
            ->assertNotFound()
            ->assertExactJson(['message' => 'Show 999999 was not found.', 'code' => 'show_not_found']);

        Http::assertSentCount(1);
    }

    public function test_it_answers_404_as_json_for_non_numeric_ids(): void
    {
        Http::fake();

        $this->get('/api/shows/abc')
            ->assertNotFound()
            ->assertHeader('content-type', 'application/json')
            ->assertJsonStructure(['message']);

        Http::assertNothingSent();
    }

    #[DataProvider('idsThatCannotExist')]
    public function test_it_answers_404_without_asking_the_upstream_for_ids_that_cannot_exist(string $id): void
    {
        Http::fake();

        $this->getJson("/api/shows/{$id}")->assertNotFound();

        Http::assertNothingSent();
    }

    /**
     * @return array<string, array{string}>
     */
    public static function idsThatCannotExist(): array
    {
        return [
            'zero' => ['0'],
            'negative' => ['-1'],
            'decimal' => ['1.5'],
            'larger than an integer' => ['99999999999999999999'],
        ];
    }

    public function test_it_answers_502_when_the_upstream_sends_an_incomplete_show(): void
    {
        Http::fake(['api.tvmaze.com/*' => Http::response(['id' => 5])]);

        $this->getJson('/api/shows/5')
            ->assertStatus(502)
            ->assertJsonPath('code', 'upstream_error');
    }
}
