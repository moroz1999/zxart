<?php

declare(strict_types=1);

namespace ZxArt\Tunes\Exception;

use RuntimeException;

final class TuneNotFoundException extends RuntimeException
{
    public static function forId(int $id): self
    {
        return new self('Tune not found: ' . $id);
    }
}
