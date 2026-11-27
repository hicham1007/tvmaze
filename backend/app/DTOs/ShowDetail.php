<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class ShowDetail
{
    /**
     * @param  list<string>  $genres
     * @param  string|null  $summary  Plain text; paragraphs are separated by a blank line.
     * @param  int|null  $runtime  Minutes per episode.
     * @param  list<string>  $scheduleDays  English day names, e.g. ["Sunday"].
     * @param  list<CastMember>  $cast  In billing order: the main characters come first.
     */
    public function __construct(
        public int $id,
        public string $name,
        public ?string $image = null,
        public array $genres = [],
        public ?float $rating = null,
        public ?string $premiered = null,
        public ?string $ended = null,
        public ?string $summary = null,
        public ?string $type = null,
        public ?string $language = null,
        public ?string $status = null,
        public ?int $runtime = null,
        public ?string $network = null,
        public ?string $country = null,
        public ?string $scheduleTime = null,
        public array $scheduleDays = [],
        public ?string $officialSite = null,
        public array $cast = [],
    ) {}

    public function withMainCast(int $limit): self
    {
        return new self(
            id: $this->id,
            name: $this->name,
            image: $this->image,
            genres: $this->genres,
            rating: $this->rating,
            premiered: $this->premiered,
            ended: $this->ended,
            summary: $this->summary,
            type: $this->type,
            language: $this->language,
            status: $this->status,
            runtime: $this->runtime,
            network: $this->network,
            country: $this->country,
            scheduleTime: $this->scheduleTime,
            scheduleDays: $this->scheduleDays,
            officialSite: $this->officialSite,
            cast: array_slice($this->cast, 0, $limit),
        );
    }
}
