<?php

declare(strict_types=1);

namespace ZxArt\Shared\Dto;

/**
 * An alias of an author or a group, with when it was used: Unix timestamps,
 * 0 when unknown.
 *
 * @psalm-api
 */
final readonly class AliasDto
{
    public function __construct(
        public int $id,
        public string $title,
        public int $startDate,
        public int $endDate,
    ) {
    }
}
