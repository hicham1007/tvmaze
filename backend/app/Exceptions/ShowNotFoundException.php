<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

final class ShowNotFoundException extends RuntimeException
{
    public function __construct(public readonly int $id)
    {
        parent::__construct("Show {$id} was not found.");
    }
}
