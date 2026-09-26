<?php

declare(strict_types=1);

namespace ZxArt\Parties\Rest;

/**
 * @psalm-api
 */
final readonly class PartyDataResultRestDto
{
    public function __construct(
        public int $id,
    ) {
    }
}
