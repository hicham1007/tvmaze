<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\TvMaze\TvMazeMapper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class TvMazeMapperTest extends TestCase
{
    public function test_a_search_result_uses_the_small_image(): void
    {
        $show = TvMazeMapper::toShow([
            'id' => 139,
            'name' => 'Girls',
            'genres' => ['Drama'],
            'premiered' => '2012-04-15',
            'rating' => ['average' => 7],
            'image' => ['medium' => 'https://img.test/m.jpg', 'original' => 'https://img.test/o.jpg'],
        ]);

        $this->assertSame(139, $show->id);
        $this->assertSame('https://img.test/m.jpg', $show->image);
        $this->assertSame(7.0, $show->rating);
        $this->assertSame(['Drama'], $show->genres);
        $this->assertSame('2012-04-15', $show->premiered);
    }

    public function test_missing_optional_data_becomes_null_or_empty(): void
    {
        $show = TvMazeMapper::toShow(['id' => 1, 'name' => 'Bare', 'image' => null, 'rating' => ['average' => null]]);

        $this->assertNull($show->image);
        $this->assertNull($show->rating);
        $this->assertNull($show->premiered);
        $this->assertSame([], $show->genres);
    }

    public function test_the_detail_uses_the_large_image_and_falls_back_to_the_small_one(): void
    {
        $both = ['medium' => 'https://img.test/m.jpg', 'original' => 'https://img.test/o.jpg'];

        $this->assertSame('https://img.test/o.jpg', TvMazeMapper::toShowDetail(['id' => 1, 'name' => 'A', 'image' => $both])->image);
        $this->assertSame('https://img.test/m.jpg', TvMazeMapper::toShowDetail(['id' => 1, 'name' => 'A', 'image' => ['medium' => $both['medium']]])->image);
    }

    public function test_streaming_shows_take_their_network_from_the_web_channel(): void
    {
        $show = TvMazeMapper::toShowDetail([
            'id' => 1,
            'name' => 'Dark',
            'network' => null,
            'webChannel' => ['name' => 'Netflix', 'country' => null],
        ]);

        $this->assertSame('Netflix', $show->network);
        $this->assertNull($show->country);
    }

    public function test_the_runtime_falls_back_to_the_average_and_an_empty_schedule_time_is_null(): void
    {
        $show = TvMazeMapper::toShowDetail([
            'id' => 1,
            'name' => 'Dark',
            'runtime' => null,
            'averageRuntime' => 53,
            'schedule' => ['time' => '', 'days' => ['Friday']],
        ]);

        $this->assertSame(53, $show->runtime);
        $this->assertNull($show->scheduleTime);
        $this->assertSame(['Friday'], $show->scheduleDays);
    }

    #[DataProvider('officialSites')]
    public function test_only_web_addresses_are_kept_as_the_official_site(mixed $given, ?string $expected): void
    {
        $show = TvMazeMapper::toShowDetail(['id' => 1, 'name' => 'A', 'officialSite' => $given]);

        $this->assertSame($expected, $show->officialSite);
    }

    /**
     * @return array<string, array{mixed, string|null}>
     */
    public static function officialSites(): array
    {
        return [
            'https' => ['https://www.netflix.com/title/80100172', 'https://www.netflix.com/title/80100172'],
            'http' => ['http://www.hbo.com/game-of-thrones', 'http://www.hbo.com/game-of-thrones'],
            'upper case scheme' => ['HTTPS://example.com', 'HTTPS://example.com'],
            'script' => ['javascript:alert(document.cookie)', null],
            'data' => ['data:text/html,<script>alert(1)</script>', null],
            'other scheme' => ['ftp://example.com/file', null],
            'not an address' => ['see the network page', null],
            'missing' => [null, null],
            'not a string' => [42, null],
        ];
    }

    public function test_the_runtime_is_always_whole_minutes(): void
    {
        $runtime = fn (mixed $value) => TvMazeMapper::toShowDetail(['id' => 1, 'name' => 'A', 'runtime' => $value])->runtime;

        $this->assertSame(60, $runtime(60));
        $this->assertSame(43, $runtime(42.5));
        $this->assertSame(30, $runtime('30'));
        $this->assertNull($runtime('unknown'));
        $this->assertNull($runtime(null));
    }

    public function test_the_cast_keeps_its_order_and_uses_the_character_image_when_the_person_has_none(): void
    {
        $show = TvMazeMapper::toShowDetail([
            'id' => 1,
            'name' => 'Dark',
            '_embedded' => ['cast' => [
                ['person' => ['id' => 10, 'name' => 'A', 'image' => ['medium' => 'p.jpg']], 'character' => ['name' => 'X', 'image' => ['medium' => 'c.jpg']]],
                ['person' => ['id' => 20, 'name' => 'B', 'image' => null], 'character' => ['name' => 'Y', 'image' => ['medium' => 'c2.jpg']]],
                ['person' => ['id' => 30, 'name' => 'C', 'image' => null], 'character' => ['name' => 'Z', 'image' => null]],
            ]],
        ]);

        $this->assertSame([10, 20, 30], array_column($show->cast, 'personId'));
        $this->assertSame(['X', 'Y', 'Z'], array_column($show->cast, 'character'));
        $this->assertSame(['p.jpg', 'c2.jpg', null], array_column($show->cast, 'image'));
    }

    #[DataProvider('summaries')]
    public function test_the_html_summary_becomes_plain_text(?string $html, ?string $expected): void
    {
        $show = TvMazeMapper::toShowDetail(['id' => 1, 'name' => 'A', 'summary' => $html]);

        $this->assertSame($expected, $show->summary);
    }

    /**
     * @return array<string, array{string|null, string|null}>
     */
    public static function summaries(): array
    {
        return [
            'no summary' => [null, null],
            'only markup' => ['<p> </p>', null],
            'inline tags are dropped' => ['<p>A <b>bold</b> and <i>italic</i> story.</p>', 'A bold and italic story.'],
            'paragraphs are kept' => ["<p>One.</p>\n<p>Two.</p>", "One.\n\nTwo."],
            'line breaks are kept' => ['<p>One.<br />Two.</p>', "One.\n\nTwo."],
            'entities are decoded' => ['<p>Tom &amp; Jerry&#039;s &quot;show&quot;</p>', 'Tom & Jerry\'s "show"'],
            'scripts do not survive as markup' => ['<p>Hi<script>alert(1)</script></p>', 'Hialert(1)'],
        ];
    }
}
