<?php

declare(strict_types=1);

namespace ZxArt\Tunes\Rest;

/**
 * @psalm-api
 */
final readonly class TuneDataResultRestDto
{
    public function __construct(
        public int $id,
    ) {
    }
}
