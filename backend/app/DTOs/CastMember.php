<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class CastMember
{
    public function __construct(
        public ?int $personId,
        public ?string $name,
        public ?string $character,
        public ?string $image = null,
    ) {}
}
