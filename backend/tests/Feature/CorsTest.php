<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CorsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
        Http::fake(['api.tvmaze.com/*' => Http::response([])]);
    }

    public function test_cors_is_closed_by_default(): void
    {
        $this->getJson('/api/shows?q=dark', ['Origin' => 'https://evil.example'])
            ->assertOk()
            ->assertHeaderMissing('Access-Control-Allow-Origin');

        // The preflight a browser sends first gets no permission either.
        $this->call('OPTIONS', '/api/shows', server: [
            'HTTP_ORIGIN' => 'https://evil.example',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'GET',
        ])->assertHeaderMissing('Access-Control-Allow-Origin');
    }

    public function test_an_origin_listed_in_the_configuration_is_allowed(): void
    {
        config(['cors.allowed_origins' => ['https://app.example.com']]);

        $this->getJson('/api/shows?q=dark', ['Origin' => 'https://app.example.com'])
            ->assertOk()
            ->assertHeader('Access-Control-Allow-Origin', 'https://app.example.com');

        // Any other site is still refused: the browser only accepts a response
        // whose Access-Control-Allow-Origin matches the page that asked.
        $other = $this->getJson('/api/shows?q=dark', ['Origin' => 'https://evil.example']);

        $this->assertNotContains($other->headers->get('Access-Control-Allow-Origin'), ['https://evil.example', '*']);
    }
}
