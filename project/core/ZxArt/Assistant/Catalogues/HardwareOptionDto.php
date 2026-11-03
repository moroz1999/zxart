<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Catalogues;

/**
 * @psalm-api
 */
final readonly class HardwareOptionDto
{
    public function __construct(
        public string $code,
        public string $name,
    ) {
    }
}
