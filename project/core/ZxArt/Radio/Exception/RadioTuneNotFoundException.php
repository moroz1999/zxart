<?php

declare(strict_types=1);

namespace ZxArt\Radio\Exception;

use RuntimeException;

final class RadioTuneNotFoundException extends RuntimeException
{
    public static function forCriteria(): self
    {
        return new self('No tune found for the selected criteria.');
    }

    public static function forId(int $id): self
    {
        return new self('Tune not found: ' . $id);
    }
}
