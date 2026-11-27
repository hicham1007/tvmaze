<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class Show
{
    /**
     * @param  list<string>  $genres
     * @param  float|null  $rating  Average score from 0 to 10, null when nobody has rated it.
     * @param  string|null  $premiered  ISO date (YYYY-MM-DD).
     */
    public function __construct(
        public int $id,
        public string $name,
        public ?string $image = null,
        public array $genres = [],
        public ?float $rating = null,
        public ?string $premiered = null,
    ) {}
}
