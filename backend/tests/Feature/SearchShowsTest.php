<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SearchShowsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
    }

    public function test_it_returns_the_shows_matching_the_query(): void
    {
        Http::fake([
            'api.tvmaze.com/search/shows?q=girls' => Http::response([
                ['score' => 0.9, 'show' => [
                    'id' => 139,
                    'name' => 'Girls',
                    'genres' => ['Drama', 'Romance'],
                    'premiered' => '2012-04-15',
                    'rating' => ['average' => 6.5],
                    'image' => ['medium' => 'https://img.test/139-m.jpg', 'original' => 'https://img.test/139-o.jpg'],
                    'summary' => '<p>Ignored in the list.</p>',
                ]],
                ['score' => 0.5, 'show' => [
                    'id' => 41734,
                    'name' => 'GIRLS',
                    'genres' => [],
                    'premiered' => null,
                    'rating' => ['average' => null],
                    'image' => null,
                ]],
            ]),
        ]);

        $this->getJson('/api/shows?q=Girls')
            ->assertOk()
            ->assertExactJson(['data' => [
                [
                    'id' => 139,
                    'name' => 'Girls',
                    'image' => 'https://img.test/139-m.jpg',
                    'genres' => ['Drama', 'Romance'],
                    'rating' => 6.5,
                    'premiered' => '2012-04-15',
                ],
                [
                    'id' => 41734,
                    'name' => 'GIRLS',
                    'image' => null,
                    'genres' => [],
                    'rating' => null,
                    'premiered' => null,
                ],
            ]]);
    }

    public function test_it_identifies_itself_to_the_upstream(): void
    {
        Http::fake(['api.tvmaze.com/*' => Http::response([])]);

        $this->getJson('/api/shows?q=dark')->assertOk();

        Http::assertSent(fn (Request $request) => $request->hasHeader('User-Agent', 'series-api/1.0'));
    }

    public function test_it_returns_an_empty_list_when_nothing_matches(): void
    {
        Http::fake(['api.tvmaze.com/*' => Http::response([])]);

        $this->getJson('/api/shows?q=zzzzzz')
            ->assertOk()
            ->assertExactJson(['data' => []]);
    }

    public function test_it_accepts_a_single_character(): void
    {
        Http::fake([
            'api.tvmaze.com/search/shows?q=x' => Http::response([['show' => ['id' => 9, 'name' => 'X']]]),
        ]);

        $this->getJson('/api/shows?q=X')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'X');
    }

    public function test_it_validates_the_search_term(): void
    {
        Http::fake();

        $this->getJson('/api/shows')->assertUnprocessable()->assertJsonValidationErrors('q');
        $this->getJson('/api/shows?q=')->assertUnprocessable()->assertJsonValidationErrors('q');
        $this->getJson('/api/shows?q=%20%20')->assertUnprocessable()->assertJsonValidationErrors('q');
        $this->getJson('/api/shows?q='.str_repeat('a', 101))->assertUnprocessable()->assertJsonValidationErrors('q');
        $this->getJson('/api/shows?q[]=dark')->assertUnprocessable()->assertJsonValidationErrors('q');

        Http::assertNothingSent();
    }

    public function test_it_accepts_a_term_of_exactly_the_maximum_length(): void
    {
        Http::fake(['api.tvmaze.com/*' => Http::response([])]);

        $this->getJson('/api/shows?q='.str_repeat('a', 100))->assertOk();
    }

    public function test_it_sends_accents_and_symbols_to_the_upstream_untouched(): void
    {
        Http::fake(['api.tvmaze.com/*' => Http::response([])]);

        $this->getJson('/api/shows?q='.urlencode('Señor & Co'))->assertOk();

        Http::assertSent(fn (Request $request) => $request['q'] === 'señor & co');
    }

    public function test_it_skips_results_the_upstream_sends_incomplete(): void
    {
        Http::fake(['api.tvmaze.com/*' => Http::response([
            ['show' => ['name' => 'No id']],
            ['show' => ['id' => 7]],
            ['show' => null],
            ['score' => 0.4],
            ['show' => ['id' => 2, 'name' => 'Dark']],
        ])]);

        $this->getJson('/api/shows?q=dark')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Dark');
    }

    public function test_it_only_accepts_get(): void
    {
        Http::fake();

        $this->postJson('/api/shows?q=dark')
            ->assertMethodNotAllowed()
            ->assertJsonStructure(['message']);

        Http::assertNothingSent();
    }

    public function test_equivalent_searches_hit_the_upstream_only_once(): void
    {
        Http::fake(['api.tvmaze.com/*' => Http::response([])]);

        $this->getJson('/api/shows?q=Lost')->assertOk();
        $this->getJson('/api/shows?q=%20lost%20')->assertOk();

        Http::assertSentCount(1);
    }

    public function test_it_retries_after_a_transient_upstream_error(): void
    {
        Http::fake(['api.tvmaze.com/*' => Http::sequence()
            ->push('Bad gateway', 502)
            ->push([['show' => ['id' => 1, 'name' => 'Under the Dome']]]),
        ]);

        $this->getJson('/api/shows?q=dome')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Under the Dome');

        Http::assertSentCount(2);
    }

    public function test_it_answers_502_when_the_upstream_keeps_failing(): void
    {
        Http::fake(['api.tvmaze.com/*' => Http::response('Server error', 500)]);

        $this->getJson('/api/shows?q=dome')
            ->assertStatus(502)
            ->assertJsonPath('code', 'upstream_error')
            ->assertJsonStructure(['message', 'code']);

        Http::assertSentCount(2);
    }

    public function test_it_answers_502_when_the_upstream_body_is_not_json(): void
    {
        Http::fake(['api.tvmaze.com/*' => Http::response('<html>maintenance</html>')]);

        $this->getJson('/api/shows?q=dome')
            ->assertStatus(502)
            ->assertJsonPath('code', 'upstream_error');
    }

    public function test_it_answers_503_when_the_upstream_rate_limits_us(): void
    {
        Http::fake(['api.tvmaze.com/*' => Http::response('', 429)]);

        $this->getJson('/api/shows?q=dome')
            ->assertStatus(503)
            ->assertJsonPath('code', 'upstream_rate_limited');

        // A 429 is not retried: insisting would only make it worse.
        Http::assertSentCount(1);
    }

    public function test_it_answers_504_when_the_upstream_is_unreachable(): void
    {
        Http::fake(fn () => throw new ConnectionException('cURL error 28: Operation timed out'));

        $this->getJson('/api/shows?q=dome')
            ->assertStatus(504)
            ->assertJsonPath('code', 'upstream_unreachable');
    }
}
