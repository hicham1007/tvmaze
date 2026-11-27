<?php

declare(strict_types=1);

namespace App\Services\TvMaze;

use App\DTOs\CastMember;
use App\DTOs\Show;
use App\DTOs\ShowDetail;

final class TvMazeMapper
{
    /**
     * The minimum a payload needs to be a show we can work with.
     *
     * @phpstan-assert-if-true array<string, mixed> $data
     */
    public static function isShow(mixed $data): bool
    {
        return is_array($data) && is_int($data['id'] ?? null) && is_string($data['name'] ?? null);
    }

    /**
     * @param  array<string, mixed>  $show
     */
    public static function toShow(array $show): Show
    {
        return new Show(
            id: (int) $show['id'],
            name: (string) $show['name'],
            image: $show['image']['medium'] ?? null,
            genres: $show['genres'] ?? [],
            rating: self::rating($show),
            premiered: $show['premiered'] ?? null,
        );
    }

    /**
     * @param  array<string, mixed>  $show  A show with its cast embedded (?embed=cast).
     */
    public static function toShowDetail(array $show): ShowDetail
    {
        // Broadcast shows have a "network"; streaming originals have a "webChannel".
        $channel = $show['network'] ?? $show['webChannel'] ?? null;

        return new ShowDetail(
            id: (int) $show['id'],
            name: (string) $show['name'],
            image: $show['image']['original'] ?? $show['image']['medium'] ?? null,
            genres: $show['genres'] ?? [],
            rating: self::rating($show),
            premiered: $show['premiered'] ?? null,
            ended: $show['ended'] ?? null,
            summary: self::plainText($show['summary'] ?? null),
            type: $show['type'] ?? null,
            language: $show['language'] ?? null,
            status: $show['status'] ?? null,
            runtime: self::minutes($show['runtime'] ?? $show['averageRuntime'] ?? null),
            network: $channel['name'] ?? null,
            country: $channel['country']['name'] ?? null,
            scheduleTime: ($show['schedule']['time'] ?? '') ?: null,
            scheduleDays: $show['schedule']['days'] ?? [],
            officialSite: self::webUrl($show['officialSite'] ?? null),
            cast: array_map(self::toCastMember(...), array_values($show['_embedded']['cast'] ?? [])),
        );
    }

    /**
     * @param  array<string, mixed>  $member
     */
    private static function toCastMember(array $member): CastMember
    {
        return new CastMember(
            personId: $member['person']['id'] ?? null,
            name: $member['person']['name'] ?? null,
            character: $member['character']['name'] ?? null,
            image: $member['person']['image']['medium'] ?? $member['character']['image']['medium'] ?? null,
        );
    }

    /**
     * @param  array<string, mixed>  $show
     */
    private static function rating(array $show): ?float
    {
        $average = $show['rating']['average'] ?? null;

        return $average === null ? null : (float) $average;
    }

    /**
     * Only http(s) addresses are passed on. This value ends up as the target of
     * a link, and anything else (javascript:, data:...) could run code in the browser.
     */
    private static function webUrl(mixed $value): ?string
    {
        if (! is_string($value) || filter_var($value, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        return in_array(strtolower((string) parse_url($value, PHP_URL_SCHEME)), ['http', 'https'], true) ? $value : null;
    }

    private static function minutes(mixed $value): ?int
    {
        return is_numeric($value) ? (int) round((float) $value) : null;
    }

    /**
     * TVMaze summaries are HTML. We keep plain text (paragraphs separated by a
     * blank line) so no client ever has to inject third-party markup.
     */
    private static function plainText(?string $html): ?string
    {
        if ($html === null) {
            return null;
        }

        $text = preg_replace('#</p>\s*<p[^>]*>|<br\s*/?>#i', "\n\n", $html) ?? $html;
        $text = trim(html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5));

        return $text === '' ? null : $text;
    }
}
