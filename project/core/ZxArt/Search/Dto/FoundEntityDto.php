<?php

declare(strict_types=1);

namespace ZxArt\Search\Dto;

use ZxArt\Shared\EntityType;

/**
 * An entity a phrase search found, with what tells it apart from others of
 * the same title: the year, and a short qualifier (an author's real name and
 * country, a prod's groups, a release's prod, an alias's owner).
 *
 * @psalm-api
 */
final readonly class FoundEntityDto
{
    public function __construct(
        public EntityType $type,
        public int $id,
        public string $title,
        public ?int $year,
        public string $qualifier,
    ) {
    }
}
